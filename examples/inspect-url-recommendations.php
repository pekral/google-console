<?php

declare(strict_types = 1);

/**
 * Example: inspect a URL and display human-readable recommendations from the audit result.
 *
 * When the indexing check result is NOT_INDEXED or UNKNOWN, the library derives short,
 * actionable recommendations from reason_codes (e.g. meta noindex, robots.txt, non-200,
 * canonical, indexing request in GSC). These are available in IndexingCheckResult::recommendations.
 *
 * This script calls GoogleConsole::inspectUrl(), then prints primary status, reason codes,
 * and the recommendations list. Use this when you need to show users what to do next
 * (e.g. in reports, dashboards, or CLI tools).
 *
 * Recommendations are also included in:
 * - IndexingCheckResult::toArray() as 'recommendations' (for JSON/API output)
 * - The pekral:google-url-inspect command output (section "Business output (indexing check)")
 *
 * Test domain: pekral.cz
 *
 * Run:
 *   GOOGLE_CREDENTIALS_PATH=/path/to/credentials.json php examples/inspect-url-recommendations.php
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

$siteUrl = 'sc-domain:pekral.cz';
$inspectionUrl = 'https://pekral.cz/';

echo sprintf('Inspecting URL: %s%s', $inspectionUrl, PHP_EOL);
echo str_repeat('─', 60) . "\n";

$result = $console->inspectUrl($siteUrl, $inspectionUrl);

if ($result->indexingCheckResult === null) {
    echo "No indexing check result (API response had no index status data).\n";
    exit(0);
}

$check = $result->indexingCheckResult;

echo sprintf("Primary status: %s\n", $check->primaryStatus->value);
echo sprintf(
    "Reason codes:   %s\n",
    implode(', ', array_map(static fn (IndexingCheckReasonCode $c): string => $c->value, $check->reasonCodes)),
);

if ($check->recommendations !== []) {
    echo "\nRecommendations:\n";

    foreach ($check->recommendations as $i => $rec) {
        echo sprintf("  %d. %s\n", $i + 1, $rec);
    }
} else {
    echo "\nRecommendations: (none for this result)\n";
}
