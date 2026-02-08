<?php

declare(strict_types = 1);

/**
 * Example: use the library directly to inspect a URL and read the business output model.
 *
 * This script calls GoogleConsole::inspectUrl() and then prints the structured
 * indexing check result (IndexingCheckResult) when present. Use this pattern when you
 * need to programmatically act on primary status, confidence, or reason codes (e.g.
 * monitoring, batch checks, or custom reporting).
 *
 * The business output model provides:
 * - primaryStatus: INDEXED | NOT_INDEXED | UNKNOWN
 * - confidence: high | medium | low
 * - reason_codes: list of machine-readable reasons (e.g. INDEXED_CONFIRMED, ROBOTS_BLOCKED)
 * - checked_at: timestamp of evaluation
 * - source_type: authoritative | heuristic
 *
 * Test domain: pekral.cz
 *
 * Run:
 *   GOOGLE_CREDENTIALS_PATH=/path/to/credentials.json php examples/inspect-url-business-model.php
 */

require __DIR__ . '/bootstrap.php';

use Pekral\GoogleConsole\Enum\IndexingCheckReasonCode;
use Pekral\GoogleConsole\GoogleConsole;

$console = GoogleConsole::fromCredentialsPath($credentials);
$result = $console->inspectUrl('sc-domain:pekral.cz', 'https://pekral.cz/');

if ($result->indexingCheckResult === null) {
    echo "No indexing check result (API response had no index status data).\n";
    exit(0);
}

$check = $result->indexingCheckResult;

echo "Business output (indexing check)\n";
echo str_repeat('─', 60) . "\n";
echo sprintf("Primary status: %s  (INDEXED = in Google index, NOT_INDEXED = excluded, UNKNOWN = inconclusive)\n", $check->primaryStatus->value);
echo sprintf("Confidence:     %s  (how reliable the result is: high | medium | low)\n", $check->confidence->value);
echo sprintf(
    "Reason codes:   %s  (machine-readable causes; e.g. INDEXED_CONFIRMED, ROBOTS_BLOCKED)\n",
    implode(', ', array_map(static fn (IndexingCheckReasonCode $c): string => $c->value, $check->reasonCodes)),
);
echo sprintf("Checked at:     %s  (when this evaluation was performed)\n", $check->checkedAt->format('Y-m-d H:i:s'));
echo sprintf("Source type:    %s  (authoritative = from API, heuristic = inferred)\n", $check->sourceType->value);
