<?php

declare(strict_types = 1);

/**
 * Example: inspect a URL for indexing status and mobile usability.
 *
 * Outputs: indexing status, business output (primary status, confidence, reason codes),
 * canonical URLs, mobile usability.
 *
 * Optional: --mode=strict (default) or --mode=best-effort.
 * Optional: --json for JSON output.
 *
 * Run: GOOGLE_CREDENTIALS_PATH=/path/to/credentials.json php examples/inspect-url.php
 */

require __DIR__ . '/bootstrap.php';

use Pekral\GoogleConsole\Enum\IndexingCheckReasonCode;
use Pekral\GoogleConsole\Enum\OperatingMode;
use Pekral\GoogleConsole\Factory\GoogleConsoleFactory;

$mode = OperatingMode::STRICT;
$json = false;

$argv ??= [];

foreach ($argv as $i => $arg) {
    if ($arg === '--json' || $arg === '-j') {
        $json = true;
    }

    if (str_starts_with($arg, '--mode=')) {
        $mode = OperatingMode::from(substr($arg, 7));
    }

    if ($arg === '--mode' && isset($argv[$i + 1])) {
        $mode = OperatingMode::from($argv[$i + 1]);
    }
}

$console = GoogleConsoleFactory::fromCredentialsPath($credentials);
$result = $console->inspectUrl('sc-domain:pekral.cz', 'https://pekral.cz/', $mode);

if ($json) {
    echo json_encode($result->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
    exit(0);
}

echo "Google Search Console - URL Inspection\n";
echo str_repeat('─', 60) . "\n\n";
echo "Indexing Status\n";
echo '  Verdict         ' . $result->verdict . "\n";
echo '  Is Indexed      ' . ($result->isIndexed() ? 'Yes' : 'No') . "\n";
echo '  Coverage State  ' . $result->coverageState . "\n";
echo '  Last Crawl     ' . ($result->lastCrawlTime?->format('Y-m-d H:i:s') ?? 'N/A') . "\n";

if ($result->indexingCheckResult !== null) {
    $check = $result->indexingCheckResult;
    echo "\nBusiness output (indexing check)\n";
    echo '  Primary status  ' . $check->primaryStatus->value . "\n";
    echo '  Confidence      ' . $check->confidence->value . "\n";
    echo '  Reason codes    ' . implode(', ', array_map(static fn (IndexingCheckReasonCode $c) => $c->value, $check->reasonCodes)) . "\n";
}

echo "\nCanonical URLs\n";
echo '  Google  ' . ($result->googleCanonical ?? 'N/A') . "\n";
echo '  User    ' . ($result->userCanonical ?? 'N/A') . "\n";
echo "\nMobile Usability\n";
echo '  Mobile Friendly  ' . ($result->isMobileFriendly ? 'Yes' : 'No') . "\n";

if ($result->mobileUsabilityIssue !== null) {
    echo '  Issues           ' . $result->mobileUsabilityIssue . "\n";
}
