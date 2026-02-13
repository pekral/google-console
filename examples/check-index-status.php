<?php

declare(strict_types = 1);

/**
 * Example: Index Status Checker – business API for status-only checks.
 *
 * Calls GoogleConsole::checkIndexStatus() and prints IndexStatusCheckResult
 * (url, status, reason_codes, confidence, checked_at, source_type).
 * Use for monitoring, health checks, or when you only need indexing status.
 *
 * For full inspection (mobile usability, canonicals, coverage state) use inspectUrl() instead.
 *
 * Run:
 *   GOOGLE_CREDENTIALS_PATH=/path/to/credentials.json php examples/check-index-status.php
 */

require __DIR__ . '/bootstrap.php';

use Pekral\GoogleConsole\Config\GoogleConfig;
use Pekral\GoogleConsole\Enum\IndexingCheckReasonCode;
use Pekral\GoogleConsole\Factory\GoogleClientFactory;
use Pekral\GoogleConsole\GoogleConsole;
use Pekral\GoogleConsole\UrlNormalizer\UrlNormalizationRules;
use Pekral\GoogleConsole\UrlNormalizer\UrlNormalizer;

$config = GoogleConfig::fromCredentialsPath($credentials);
$client = new GoogleClientFactory()->create($config);
$normalizer = new UrlNormalizer(UrlNormalizationRules::forApiCalls());
$console = new GoogleConsole($client, urlNormalizer: $normalizer);

$result = $console->checkIndexStatus('sc-domain:pekral.cz', 'https://pekral.cz/');

echo "Index Status Check\n";
echo str_repeat('─', 60) . "\n";
echo sprintf("URL:         %s\n", $result->url);
echo sprintf("Status:      %s  (INDEXED | NOT_INDEXED | UNKNOWN)\n", $result->status->value);
echo sprintf("Confidence:  %s\n", $result->confidence->value);
echo sprintf(
    "Reason codes: %s\n",
    implode(', ', array_map(static fn (IndexingCheckReasonCode $c) => $c->value, $result->reasonCodes)),
);
echo sprintf("Checked at:  %s\n", $result->checkedAt->format('Y-m-d H:i:s'));
echo sprintf("Source type: %s\n", $result->sourceType->value);
