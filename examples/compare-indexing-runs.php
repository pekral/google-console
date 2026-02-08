<?php

declare(strict_types = 1);

/**
 * Example: compare two indexing runs (e.g. previous vs current) for monitoring.
 *
 * Runs inspectBatchUrls twice and compares results. In practice you would store
 * the first run (e.g. in cache or DB) and later compare with a new run to detect
 * NEWLY_INDEXED, DROPPED_FROM_INDEX, BECAME_UNKNOWN, RECOVERED_FROM_UNKNOWN.
 *
 * Run:
 *   GOOGLE_CREDENTIALS_PATH=/path/to/credentials.json php examples/compare-indexing-runs.php
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

$config = GoogleConfig::fromCredentialsPath($credentials);
$client = new GoogleClientFactory()->create($config);
$console = new GoogleConsole($client);

echo "Run 1 (previous)...\n";
$previous = $console->inspectBatchUrls($siteUrl, $urls);

echo "Run 2 (current)...\n";
$current = $console->inspectBatchUrls($siteUrl, $urls);

$comparison = $console->compareIndexingRuns($previous, $current);

echo "\nIndexing run comparison\n";
echo str_repeat('─', 60) . "\n";
echo 'Changes: ' . count($comparison->changes) . "\n";
echo 'Indexed delta: ' . $comparison->indexedDelta . "\n";
echo 'Not indexed delta: ' . $comparison->notIndexedDelta . "\n";
echo 'Unknown delta: ' . $comparison->unknownDelta . "\n";

if ($comparison->changes !== []) {
    echo "\nChanges by URL\n";

    foreach ($comparison->changes as $change) {
        echo '  ' . $change->url . ' => ' . $change->changeType->value;
        echo ' (' . $change->previousStatus->value . ' -> ' . $change->currentStatus->value . ")\n";
    }
}

if ($comparison->dominantReasonCodes !== []) {
    echo "\nDominant reason codes (current run)\n";

    foreach ($comparison->dominantReasonCodes as $code => $count) {
        echo '  ' . $code . ': ' . $count . "\n";
    }
}
