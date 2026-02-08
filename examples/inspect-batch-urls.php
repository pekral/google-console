<?php

declare(strict_types = 1);

/**
 * Example: batch URL inspection with aggregation and critical URL checks.
 *
 * Calls GoogleConsole::inspectBatchUrls() with a list of URLs and optional
 * critical URLs. Prints:
 * - Per-URL results (URL => INDEXED | NOT_INDEXED | UNKNOWN)
 * - Aggregation: counts for indexed / not indexed / unknown, reason code overview
 * - Critical URLs (if any) with their status
 * - Batch verdict: PASS (all critical URLs INDEXED or no critical URLs) or FAIL (any critical URL NOT_INDEXED)
 *
 * Run:
 *   GOOGLE_CREDENTIALS_PATH=/path/to/credentials.json php examples/inspect-batch-urls.php
 *
 * With critical URLs (batch fails if any of these is NOT_INDEXED):
 *   GOOGLE_CREDENTIALS_PATH=/path/to/credentials.json php examples/inspect-batch-urls.php --critical=https://pekral.cz/,https://pekral.cz/blog
 *
 * Test domain: pekral.cz
 */

require __DIR__ . '/bootstrap.php';

use Pekral\GoogleConsole\Config\GoogleConfig;
use Pekral\GoogleConsole\Factory\GoogleClientFactory;
use Pekral\GoogleConsole\GoogleConsole;

$siteUrl = 'sc-domain:pekral.cz';
$urls = [
    'https://pekral.cz/',
    'https://pekral.cz/blog',
];

$criticalUrls = [];

$argv ??= [];

foreach ($argv as $i => $arg) {
    if ($arg === '--critical' && isset($argv[$i + 1])) {
        $criticalUrls = array_filter(array_map('trim', explode(',', $argv[$i + 1])));

        break;
    }

    if (str_starts_with($arg, '--critical=')) {
        $criticalUrls = array_filter(array_map('trim', explode(',', substr($arg, 11))));

        break;
    }
}

$config = GoogleConfig::fromCredentialsPath($credentials);
$client = new GoogleClientFactory()->create($config);
$console = new GoogleConsole($client);

$result = $console->inspectBatchUrls($siteUrl, $urls, $criticalUrls);

echo "Batch URL inspection\n";
echo str_repeat('─', 60) . "\n";
echo 'Batch verdict: ' . $result->batchVerdict->value . "\n";
echo "\nAggregation\n";
echo '  Indexed:    ' . $result->aggregation->indexedCount . "\n";
echo '  Not indexed: ' . $result->aggregation->notIndexedCount . "\n";
echo '  Unknown:    ' . $result->aggregation->unknownCount . "\n";

if ($result->aggregation->reasonCodeCounts !== []) {
    echo "  Reason codes (overview):\n";

    foreach ($result->aggregation->reasonCodeCounts as $code => $count) {
        echo '    ' . $code . ': ' . $count . "\n";
    }
}

echo "\nPer-URL results\n";

foreach ($result->perUrlResults as $url => $perUrl) {
    echo '  ' . $url . ' => ' . $perUrl->status->value . "\n";
}

if ($result->criticalUrlResults !== []) {
    echo "\nCritical URLs\n";

    foreach ($result->criticalUrlResults as $perUrl) {
        echo '  ' . $perUrl->url . ' => ' . $perUrl->status->value . "\n";
    }
}
