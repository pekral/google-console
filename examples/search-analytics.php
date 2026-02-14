<?php

declare(strict_types = 1);

/**
 * Example: search analytics for a site (queries, pages, date).
 * Test domain: pekral.cz
 *
 * Run: GOOGLE_CREDENTIALS_PATH=/path/to/credentials.json php examples/search-analytics.php
 */

require __DIR__ . '/bootstrap.php';

use Pekral\GoogleConsole\Factory\GoogleConsoleFactory;

$console = GoogleConsoleFactory::fromCredentialsPath($credentials);

$startDate = new DateTimeImmutable('-30 days');
$endDate = new DateTimeImmutable();

$rows = $console->getSearchAnalytics(
    siteUrl: 'sc-domain:pekral.cz',
    startDate: $startDate,
    endDate: $endDate,
    dimensions: ['query'],
    rowLimit: 100,
);

echo "Google Search Console - Analytics\n";
echo str_repeat('─', 60) . "\n\n";
echo sprintf("Search Performance (%s - %s)\n\n", $startDate->format('Y-m-d'), $endDate->format('Y-m-d'));
printf("%-15s %10s %15s %10s %12s\n", 'query', 'Clicks', 'Impressions', 'CTR', 'Position');
echo str_repeat('-', 65) . "\n";

foreach ($rows as $row) {
    printf("%-15s %10d %15d %9.2f%% %12.1f\n", $row->query ?? '', $row->clicks, $row->impressions, $row->ctr * 100, $row->position);
}

echo "\nFound " . count($rows) . " row(s).\n";
