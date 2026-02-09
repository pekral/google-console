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
use Pekral\GoogleConsole\Config\BatchConfig;
use Pekral\GoogleConsole\Config\GoogleConfig;
use Pekral\GoogleConsole\DataBuilder\BatchAggregationBuilder;
use Pekral\GoogleConsole\DataBuilder\RequestDataBuilder;
use Pekral\GoogleConsole\DataBuilder\SiteDataBuilder;
use Pekral\GoogleConsole\DataBuilder\UrlInspectionDataBuilder;
use Pekral\GoogleConsole\DTO\BatchUrlInspectionResult;
use Pekral\GoogleConsole\DTO\IndexingComparisonResult;
use Pekral\GoogleConsole\DTO\IndexingResult;
use Pekral\GoogleConsole\DTO\PerUrlInspectionResult;
use Pekral\GoogleConsole\DTO\SearchAnalyticsRow;
use Pekral\GoogleConsole\DTO\Site;
use Pekral\GoogleConsole\DTO\UrlInspectionResult;
use Pekral\GoogleConsole\Enum\BatchVerdict;
use Pekral\GoogleConsole\Enum\IndexingCheckStatus;
use Pekral\GoogleConsole\Enum\IndexingNotificationType;
use Pekral\GoogleConsole\Enum\OperatingMode;
use Pekral\GoogleConsole\Exception\GoogleConsoleFailure;
use Pekral\GoogleConsole\Factory\GoogleClientFactory;
use Pekral\GoogleConsole\Handler\BatchFailureHandler;
use Pekral\GoogleConsole\Helper\TypeHelper;
use Pekral\GoogleConsole\UrlNormalizer\UrlNormalizer;
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
        private readonly ?UrlNormalizer $urlNormalizer = null,
        private readonly BatchAggregationBuilder $batchAggregationBuilder = new BatchAggregationBuilder(),
        private readonly IndexingRunComparator $indexingRunComparator = new IndexingRunComparator(),
        private readonly ?BatchConfig $batchConfig = null,
        private readonly BatchFailureHandler $batchFailureHandler = new BatchFailureHandler(),
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
     * @param \Pekral\GoogleConsole\Enum\OperatingMode|null $operatingMode strict (default) or best-effort
     * @return \Pekral\GoogleConsole\DTO\UrlInspectionResult Detailed inspection result with indexing and mobile status
     * @throws \Pekral\GoogleConsole\Exception\GoogleConsoleFailure When the inspection request fails
     */
    public function inspectUrl(string $siteUrl, string $inspectionUrl, ?OperatingMode $operatingMode = null): UrlInspectionResult
    {
        $inspectionUrl = $this->normalizeUrlIfConfigured($inspectionUrl);

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

            return $this->urlInspectionDataBuilder->fromGoogleResult($result, $operatingMode);
        } catch (Exception $e) {
            throw new GoogleConsoleFailure(
                $this->formatApiError($e, 'URL inspection failed'),
                $e->getCode(),
                $e,
            );
        }
    }

    /**
     * Inspects multiple URLs and returns per-URL results plus aggregation.
     * Batch verdict is FAIL if any critical URL is NOT_INDEXED.
     *
     * When BatchConfig is provided, enforces batch size limits, applies cooldown
     * with retries for temporary API errors, and records soft failures instead
     * of throwing exceptions for rate limits, timeouts, and server errors.
     *
     * @param string $siteUrl The site URL that owns the inspected pages
     * @param array<int, string> $urls URLs to inspect
     * @param array<int, string> $criticalUrls Subset of URLs that must be INDEXED for batch to PASS
     * @param \Pekral\GoogleConsole\Enum\OperatingMode|null $operatingMode strict (default) or best-effort
     * @throws \Pekral\GoogleConsole\Exception\BatchSizeLimitExceeded When batch size exceeds configured maximum
     * @throws \Pekral\GoogleConsole\Exception\GoogleConsoleFailure When a hard failure occurs
     */
    public function inspectBatchUrls(
        string $siteUrl,
        array $urls,
        array $criticalUrls = [],
        ?OperatingMode $operatingMode = null,
    ): BatchUrlInspectionResult {
        if ($this->batchConfig !== null) {
            $this->dataValidator->validateBatchSize(count($urls), $this->batchConfig->maxBatchSize);
        }

        $criticalSet = array_flip($criticalUrls);
        $perUrlResultsList = [];
        $perUrlResultsByUrl = [];

        foreach ($urls as $url) {
            $perUrl = $this->inspectSingleUrlForBatch($siteUrl, $url, $operatingMode);
            $perUrlResultsList[] = $perUrl;
            $perUrlResultsByUrl[$url] = $perUrl;
        }

        $aggregation = $this->batchAggregationBuilder->build($perUrlResultsList);
        $criticalUrlResults = $this->filterCriticalResults($perUrlResultsList, $criticalSet);
        $batchVerdict = $this->computeBatchVerdict($criticalUrlResults);

        return new BatchUrlInspectionResult(
            perUrlResults: $perUrlResultsByUrl,
            aggregation: $aggregation,
            criticalUrlResults: $criticalUrlResults,
            batchVerdict: $batchVerdict,
        );
    }

    /**
     * Compares two indexing runs (e.g. previous vs current) and returns changes, deltas and dominant reason codes.
     */
    public function compareIndexingRuns(BatchUrlInspectionResult $previous, BatchUrlInspectionResult $current): IndexingComparisonResult
    {
        return $this->indexingRunComparator->compare($previous->perUrlResults, $current->perUrlResults);
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
        $url = $this->normalizeUrlIfConfigured($url);

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

    private function inspectSingleUrlForBatch(string $siteUrl, string $url, ?OperatingMode $operatingMode): PerUrlInspectionResult {
        $maxAttempts = $this->batchConfig !== null
            ? $this->batchConfig->maxRetries + 1
            : 1;
        $lastException = null;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $result = $this->inspectUrl($siteUrl, $url, $operatingMode);

                return new PerUrlInspectionResult(
                    url: $url,
                    status: IndexingCheckStatus::fromUrlInspectionResult($result),
                    result: $result,
                );
            } catch (GoogleConsoleFailure $e) {
                $lastException = $e;
                $this->retrySoftFailureOrThrow($e, $attempt, $maxAttempts);
            }
        }

        return $this->batchFailureHandler->buildSoftFailureResult($url, $lastException ?? new GoogleConsoleFailure('Unexpected batch inspection error'));
    }

    /**
     * @throws \Pekral\GoogleConsole\Exception\GoogleConsoleFailure When the failure is hard (non-retryable)
     */
    private function retrySoftFailureOrThrow(GoogleConsoleFailure $exception, int $attempt, int $maxAttempts): void
    {
        if ($this->batchConfig === null || !$this->batchFailureHandler->isSoftFailure($exception)) {
            throw $exception;
        }

        if ($attempt < $maxAttempts) {
            $this->batchConfig->applyCooldown();
        }
    }

    /**
     * @param array<int, \Pekral\GoogleConsole\DTO\PerUrlInspectionResult> $perUrlResultsList
     * @param array<string, int> $criticalSet
     * @return array<int, \Pekral\GoogleConsole\DTO\PerUrlInspectionResult>
     */
    private function filterCriticalResults(array $perUrlResultsList, array $criticalSet): array
    {
        $out = [];

        foreach ($perUrlResultsList as $item) {
            if (isset($criticalSet[$item->url])) {
                $out[] = $item;
            }
        }

        return $out;
    }

    /**
     * @param array<int, \Pekral\GoogleConsole\DTO\PerUrlInspectionResult> $criticalUrlResults
     */
    private function computeBatchVerdict(array $criticalUrlResults): BatchVerdict
    {
        foreach ($criticalUrlResults as $item) {
            if ($item->status === IndexingCheckStatus::NOT_INDEXED) {
                return BatchVerdict::FAIL;
            }
        }

        return BatchVerdict::PASS;
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

    private function normalizeUrlIfConfigured(string $url): string
    {
        if ($this->urlNormalizer === null) {
            return $url;
        }

        return $this->urlNormalizer->normalize($url);
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
