<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole;

use DateTimeInterface;
use Google\Client;
use Pekral\GoogleConsole\DTO\BatchUrlInspectionResult;
use Pekral\GoogleConsole\DTO\IndexingComparisonResult;
use Pekral\GoogleConsole\DTO\IndexingResult;
use Pekral\GoogleConsole\DTO\IndexStatusCheckResult;
use Pekral\GoogleConsole\DTO\InspectionContext;
use Pekral\GoogleConsole\DTO\Site;
use Pekral\GoogleConsole\DTO\UrlInspectionResult;
use Pekral\GoogleConsole\Enum\IndexingNotificationType;
use Pekral\GoogleConsole\Enum\OperatingMode;

/**
 * Contract for Google Search Console API operations.
 */
interface ConsoleContract
{

    /**
     * Returns the underlying Google API client.
     */
    public function getClient(): Client;

    /**
     * Retrieves all sites registered in Google Search Console.
     *
     * @return array<\Pekral\GoogleConsole\DTO\Site>
     */
    public function getSiteList(): array;

    /**
     * Retrieves search analytics data for a specific site.
     *
     * @param string $siteUrl The site URL
     * @param \DateTimeInterface $startDate Start date for the analytics period
     * @param \DateTimeInterface $endDate End date for the analytics period
     * @param array<string> $dimensions Dimensions to group by
     * @param int $rowLimit Maximum number of rows to return
     * @param int $startRow Starting row offset
     * @return array<\Pekral\GoogleConsole\DTO\SearchAnalyticsRow>
     * @throws \Pekral\GoogleConsole\Exception\GoogleConsoleFailure
     */
    public function getSearchAnalytics(
        string $siteUrl,
        DateTimeInterface $startDate,
        DateTimeInterface $endDate,
        array $dimensions = ['query'],
        int $rowLimit = 1_000,
        int $startRow = 0,
    ): array;

    /**
     * Inspects a specific URL to check its indexing status.
     *
     * @param string $siteUrl The site URL that owns the inspected page
     * @param string $inspectionUrl The full URL to inspect
     * @param \Pekral\GoogleConsole\Enum\OperatingMode|null $operatingMode strict (default) or best-effort
     * @param \Pekral\GoogleConsole\DTO\InspectionContext|null $context optional request context (site, normalizer, mode)
     * @throws \Pekral\GoogleConsole\Exception\GoogleConsoleFailure
     */
    public function inspectUrl(
        string $siteUrl,
        string $inspectionUrl,
        ?OperatingMode $operatingMode = null,
        ?InspectionContext $context = null,
    ): UrlInspectionResult;

    /**
     * Checks index status of a URL and returns a business DTO (status, reason_codes, confidence, etc.).
     * For full inspection data use inspectUrl().
     *
     * @param string $siteUrl The site URL that owns the inspected page
     * @param string $url The full URL to check
     * @param \Pekral\GoogleConsole\Enum\OperatingMode|null $operatingMode strict (default) or best-effort
     * @param \Pekral\GoogleConsole\DTO\InspectionContext|null $context optional request context (site, normalizer, mode)
     * @throws \Pekral\GoogleConsole\Exception\GoogleConsoleFailure
     */
    public function checkIndexStatus(
        string $siteUrl,
        string $url,
        ?OperatingMode $operatingMode = null,
        ?InspectionContext $context = null,
    ): IndexStatusCheckResult;

    /**
     * Inspects multiple URLs and returns per-URL results plus aggregation.
     * Batch verdict is FAIL if any critical URL is NOT_INDEXED.
     *
     * @param string $siteUrl The site URL that owns the inspected pages
     * @param array<int, string> $urls URLs to inspect
     * @param array<int, string> $criticalUrls Subset of URLs that must be INDEXED for batch to PASS
     * @param \Pekral\GoogleConsole\Enum\OperatingMode|null $operatingMode strict (default) or best-effort
     * @param \Pekral\GoogleConsole\DTO\InspectionContext|null $context optional request context (site, normalizer, mode)
     * @throws \Pekral\GoogleConsole\Exception\BatchSizeLimitExceeded When batch size exceeds configured maximum
     * @throws \Pekral\GoogleConsole\Exception\GoogleConsoleFailure When a hard failure occurs
     */
    public function inspectBatchUrls(
        string $siteUrl,
        array $urls,
        array $criticalUrls = [],
        ?OperatingMode $operatingMode = null,
        ?InspectionContext $context = null,
    ): BatchUrlInspectionResult;

    /**
     * Compares two indexing runs (e.g. previous vs current) and returns changes, deltas and dominant reason codes.
     */
    public function compareIndexingRuns(BatchUrlInspectionResult $previous, BatchUrlInspectionResult $current): IndexingComparisonResult;

    /**
     * Retrieves information about a specific site.
     *
     * @param string $siteUrl The site URL to retrieve
     */
    public function getSite(string $siteUrl): Site;

    /**
     * Requests indexing for a specific URL via Google Indexing API.
     *
     * @param string $url The full URL to request indexing for
     * @param \Pekral\GoogleConsole\Enum\IndexingNotificationType $type The type of notification (URL_UPDATED or URL_DELETED)
     * @throws \Pekral\GoogleConsole\Exception\GoogleConsoleFailure
     */
    public function requestIndexing(string $url, IndexingNotificationType $type = IndexingNotificationType::URL_UPDATED): IndexingResult;

}
