<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole;

use DateTimeInterface;
use Google\Client;
use Pekral\GoogleConsole\DTO\IndexingResult;
use Pekral\GoogleConsole\DTO\Site;
use Pekral\GoogleConsole\DTO\UrlInspectionResult;
use Pekral\GoogleConsole\Enum\IndexingNotificationType;

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
        int $rowLimit = 1000,
        int $startRow = 0,
    ): array;

    /**
     * Inspects a specific URL to check its indexing status.
     *
     * @param string $siteUrl The site URL that owns the inspected page
     * @param string $inspectionUrl The full URL to inspect
     * @throws \Pekral\GoogleConsole\Exception\GoogleConsoleFailure
     */
    public function inspectUrl(string $siteUrl, string $inspectionUrl): UrlInspectionResult;

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
