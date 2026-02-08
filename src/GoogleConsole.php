<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole;

use DateTimeImmutable;
use DateTimeInterface;
use Google\Client;
use Google\Service\Exception;
use Google\Service\SearchConsole as SearchConsoleService;
use Google\Service\SearchConsole\InspectUrlIndexRequest;
use Google\Service\SearchConsole\InspectUrlIndexResponse;
use Google\Service\SearchConsole\UrlInspectionResult as GoogleUrlInspectionResult;
use Google\Service\Webmasters as WebmastersService;
use Google\Service\Webmasters\ApiDataRow;
use Google\Service\Webmasters\SearchAnalyticsQueryResponse;
use Google\Service\Webmasters\SitesListResponse;
use Google\Service\Webmasters\WmxSite;
use GuzzleHttp\Psr7\Request;
use Pekral\GoogleConsole\Config\GoogleConfig;
use Pekral\GoogleConsole\DataBuilder\RequestDataBuilder;
use Pekral\GoogleConsole\DataBuilder\SiteDataBuilder;
use Pekral\GoogleConsole\DataBuilder\UrlInspectionDataBuilder;
use Pekral\GoogleConsole\DTO\IndexingResult;
use Pekral\GoogleConsole\DTO\SearchAnalyticsRow;
use Pekral\GoogleConsole\DTO\Site;
use Pekral\GoogleConsole\DTO\UrlInspectionResult;
use Pekral\GoogleConsole\Enum\IndexingNotificationType;
use Pekral\GoogleConsole\Exception\GoogleConsoleFailure;
use Pekral\GoogleConsole\Factory\GoogleClientFactory;
use Pekral\GoogleConsole\Helper\TypeHelper;
use Pekral\GoogleConsole\Validator\DataValidator;
use Throwable;

/**
 * Provides methods to retrieve site information, search analytics data,
 * and URL inspection results from Google Search Console.
 */
final class GoogleConsole implements ConsoleContract
{

    private ?WebmastersService $webmastersService = null;

    private ?SearchConsoleService $searchConsoleService = null;

    public function __construct(
        private readonly Client $client,
        private readonly SiteDataBuilder $siteDataBuilder = new SiteDataBuilder(),
        private readonly UrlInspectionDataBuilder $urlInspectionDataBuilder = new UrlInspectionDataBuilder(),
        private readonly RequestDataBuilder $requestDataBuilder = new RequestDataBuilder(),
        private readonly DataValidator $dataValidator = new DataValidator(),
    ) {
    }

    /**
     * Creates a GoogleConsole instance from a service account credentials file.
     *
     * @param string $path Absolute path to the JSON credentials file
     * @throws \Pekral\GoogleConsole\Exception\GoogleConsoleFailure When credentials file is not found or invalid
     */
    public static function fromCredentialsPath(string $path): self
    {
        $config = GoogleConfig::fromCredentialsPath($path);

        return new self(new GoogleClientFactory()->create($config));
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * Retrieves all sites registered in Google Search Console for the authenticated account.
     *
     * @return array<\Pekral\GoogleConsole\DTO\Site> List of sites with their URLs and permission levels
     */
    public function getSiteList(): array
    {
        $sites = $this->getWebmastersService()->sites;
        assert($sites instanceof WebmastersService\Resource\Sites);

        $response = $sites->listSites();
        assert($response instanceof SitesListResponse);

        /** @var array<\Google\Service\Webmasters\WmxSite> $siteEntries */
        $siteEntries = $response->getSiteEntry() ?? [];

        return $this->siteDataBuilder->fromWmxSiteArray($siteEntries);
    }

    /**
     * Retrieves search analytics data for a specific site within a date range.
     *
     * @param string $siteUrl The site URL (e.g., 'https://example.com/' or 'sc-domain:example.com')
     * @param \DateTimeInterface $startDate Start date for the analytics period
     * @param \DateTimeInterface $endDate End date for the analytics period
     * @param array<string> $dimensions Dimensions to group by: query, page, country, device, searchAppearance, date
     * @param int $rowLimit Maximum number of rows to return (max 25000)
     * @param int $startRow Starting row offset for pagination
     * @return array<\Pekral\GoogleConsole\DTO\SearchAnalyticsRow> Search performance data grouped by dimensions
     * @throws \Pekral\GoogleConsole\Exception\GoogleConsoleFailure When invalid dimensions are provided or API fails
     */
    public function getSearchAnalytics(
        string $siteUrl,
        DateTimeInterface $startDate,
        DateTimeInterface $endDate,
        array $dimensions = ['query'],
        int $rowLimit = 1_000,
        int $startRow = 0,
    ): array {
        $this->dataValidator->validateDimensions($dimensions);

        try {
            $request = $this->requestDataBuilder->buildSearchAnalyticsRequest($startDate, $endDate, $dimensions, $rowLimit, $startRow);

            $searchAnalytics = $this->getWebmastersService()->searchanalytics;
            assert($searchAnalytics instanceof WebmastersService\Resource\Searchanalytics);

            $response = $searchAnalytics->query($siteUrl, $request);
            assert($response instanceof SearchAnalyticsQueryResponse);

            return $this->buildSearchAnalyticsRows($response->getRows() ?? [], $dimensions);
        } catch (Exception $e) {
            throw new GoogleConsoleFailure(
                $this->formatApiError($e, sprintf('Search analytics failed for site \'%s\'', $siteUrl)),
                $e->getCode(),
                $e,
            );
        }
    }

    /**
     * Inspects a specific URL to check its indexing status and mobile usability.
     *
     * @param string $siteUrl The site URL that owns the inspected page
     * @param string $inspectionUrl The full URL to inspect
     * @return \Pekral\GoogleConsole\DTO\UrlInspectionResult Detailed inspection result with indexing and mobile status
     * @throws \Pekral\GoogleConsole\Exception\GoogleConsoleFailure When the inspection request fails
     */
    public function inspectUrl(string $siteUrl, string $inspectionUrl): UrlInspectionResult
    {
        $request = new InspectUrlIndexRequest();
        $request->setInspectionUrl($inspectionUrl);
        $request->setSiteUrl($siteUrl);

        try {
            $urlInspectionIndex = $this->getSearchConsoleService()->urlInspection_index;
            assert($urlInspectionIndex instanceof SearchConsoleService\Resource\UrlInspectionIndex);

            $response = $urlInspectionIndex->inspect($request);
            assert($response instanceof InspectUrlIndexResponse);

            $result = $response->getInspectionResult();
            assert($result instanceof GoogleUrlInspectionResult);

            return $this->urlInspectionDataBuilder->fromGoogleResult($result);
        } catch (Exception $e) {
            throw new GoogleConsoleFailure(
                $this->formatApiError($e, 'URL inspection failed'),
                $e->getCode(),
                $e,
            );
        }
    }

    /**
     * Retrieves information about a specific site.
     *
     * @param string $siteUrl The site URL to retrieve
     * @return \Pekral\GoogleConsole\DTO\Site Site information including URL and permission level
     * @throws \Pekral\GoogleConsole\Exception\GoogleConsoleFailure When the site is not found or access is denied
     */
    public function getSite(string $siteUrl): Site
    {
        try {
            $sites = $this->getWebmastersService()->sites;
            assert($sites instanceof WebmastersService\Resource\Sites);

            $response = $sites->get($siteUrl);
            assert($response instanceof WmxSite);

            return $this->siteDataBuilder->fromWmxSite($response);
        } catch (Exception $e) {
            throw new GoogleConsoleFailure(
                $this->formatApiError($e, sprintf('Failed to get site \'%s\'', $siteUrl)),
                $e->getCode(),
                $e,
            );
        }
    }

    /**
     * Requests indexing for a specific URL via Google Indexing API.
     *
     * @param string $url The full URL to request indexing for
     * @param \Pekral\GoogleConsole\Enum\IndexingNotificationType $type The type of notification
     * @return \Pekral\GoogleConsole\DTO\IndexingResult Result containing the URL and notification time
     * @throws \Pekral\GoogleConsole\Exception\GoogleConsoleFailure When the indexing request fails
     */
    public function requestIndexing(string $url, IndexingNotificationType $type = IndexingNotificationType::URL_UPDATED): IndexingResult
    {
        $endpoint = 'https://indexing.googleapis.com/v3/urlNotifications:publish';

        $body = json_encode([
            'type' => $type->value,
            'url' => $url,
        ], JSON_THROW_ON_ERROR);

        $request = new Request('POST', $endpoint, ['Content-Type' => 'application/json'], $body);

        try {
            $httpClient = $this->client->authorize();
            $response = $httpClient->send($request);

            /** @var array{urlNotificationMetadata?: array{url?: string, latestUpdate?: array{notifyTime?: string}}} $data */
            $data = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

            $notifyTime = null;

            if (isset($data['urlNotificationMetadata']['latestUpdate']['notifyTime'])) {
                $notifyTime = new DateTimeImmutable($data['urlNotificationMetadata']['latestUpdate']['notifyTime']);
            }

            return new IndexingResult(url: $data['urlNotificationMetadata']['url'] ?? $url, type: $type, notifyTime: $notifyTime);
        } catch (Throwable $e) {
            throw new GoogleConsoleFailure(
                sprintf('Indexing request failed for URL \'%s\': %s', $url, $e->getMessage()),
                (int) $e->getCode(),
                $e,
            );
        }
    }

    private function getWebmastersService(): WebmastersService
    {
        $this->webmastersService ??= new WebmastersService($this->client);

        return $this->webmastersService;
    }

    private function getSearchConsoleService(): SearchConsoleService
    {
        $this->searchConsoleService ??= new SearchConsoleService($this->client);

        return $this->searchConsoleService;
    }

    private function formatApiError(Exception $e, string $context): string
    {
        $errors = $e->getErrors();

        if ($errors === null || $errors === []) {
            return sprintf('%s: %s', $context, $e->getMessage());
        }

        /** @var array{message?: string, reason?: string} $firstError */
        $firstError = reset($errors);
        $message = $firstError['message'] ?? $e->getMessage();
        $reason = $firstError['reason'] ?? 'unknown';

        return sprintf('%s: %s (reason: %s)', $context, $message, $reason);
    }

    /**
     * @param array<\Google\Service\Webmasters\ApiDataRow> $rows
     * @param array<string> $dimensions
     * @return array<\Pekral\GoogleConsole\DTO\SearchAnalyticsRow>
     */
    private function buildSearchAnalyticsRows(array $rows, array $dimensions): array
    {
        return array_map(
            fn (ApiDataRow $row): SearchAnalyticsRow => SearchAnalyticsRow::fromApiResponse([
                'clicks' => TypeHelper::toFloat($row->getClicks()),
                'ctr' => TypeHelper::toFloat($row->getCtr()),
                'impressions' => TypeHelper::toFloat($row->getImpressions()),
                'keys' => TypeHelper::toStringArray($row->getKeys()),
                'position' => TypeHelper::toFloat($row->getPosition()),
            ], $dimensions),
            $rows,
        );
    }

}
