<?php

declare(strict_types = 1);

/**
 * Example: batch URL inspection with configurable limits, cooldown, and failure handling.
 *
 * Demonstrates:
 * - BatchConfig for max batch size, cooldown, and retries
 * - Soft failure handling (rate limit, timeout) vs hard failure (invalid input)
 * - Identifying soft failures in per-URL results
 *
 * Run:
 *   GOOGLE_CREDENTIALS_PATH=/path/to/credentials.json php examples/batch-config.php
 *
 * Test domain: pekral.cz
 */

require __DIR__ . '/bootstrap.php';

use Pekral\GoogleConsole\Config\BatchConfig;
use Pekral\GoogleConsole\Config\GoogleConfig;
use Pekral\GoogleConsole\Exception\BatchSizeLimitExceeded;
use Pekral\GoogleConsole\Factory\GoogleClientFactory;
use Pekral\GoogleConsole\GoogleConsole;

$siteUrl = 'sc-domain:pekral.cz';
$urls = [
    'https://pekral.cz/',
    'https://pekral.cz/blog',
];

$config = GoogleConfig::fromCredentialsPath($credentials);
$client = new GoogleClientFactory()->create($config);

$batchConfig = new BatchConfig(maxBatchSize: 50, cooldownSeconds: 5, maxRetries: 2);

$console = new GoogleConsole($client, batchConfig: $batchConfig);

echo "Batch configuration\n";
echo str_repeat('─', 60) . "\n";
echo '  Max batch size:   ' . $batchConfig->maxBatchSize . "\n";
echo '  Cooldown seconds: ' . $batchConfig->cooldownSeconds . "\n";
echo '  Max retries:      ' . $batchConfig->maxRetries . "\n\n";

try {
    $result = $console->inspectBatchUrls($siteUrl, $urls);
} catch (BatchSizeLimitExceeded $e) {
    echo 'HARD FAILURE: ' . $e->getMessage() . "\n";
    exit(1);
}

echo 'Batch verdict: ' . $result->batchVerdict->value . "\n";
echo 'Indexed: ' . $result->aggregation->indexedCount . "\n";
echo 'Not indexed: ' . $result->aggregation->notIndexedCount . "\n";
echo 'Unknown: ' . $result->aggregation->unknownCount . "\n\n";

echo "Per-URL results\n";

foreach ($result->perUrlResults as $url => $perUrl) {
    $suffix = '';

    if ($perUrl->isSoftFailure()) {
        $reasonCode = $perUrl->result->indexingCheckResult?->reasonCodes[0]?->value ?? 'unknown';
        $suffix = ' [SOFT FAILURE: ' . $reasonCode . ']';
    }

    echo '  ' . $url . ' => ' . $perUrl->status->value . $suffix . "\n";
}
