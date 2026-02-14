<?php

declare(strict_types = 1);

/**
 * Example: request indexing for a URL (or removal with --delete).
 * Test domain: pekral.cz
 *
 * Run: GOOGLE_CREDENTIALS_PATH=/path/to/credentials.json php examples/request-indexing.php
 */

require __DIR__ . '/bootstrap.php';

use Pekral\GoogleConsole\Enum\IndexingNotificationType;
use Pekral\GoogleConsole\Factory\GoogleConsoleFactory;

$url = 'https://pekral.cz/novy-clanek';
$type = IndexingNotificationType::URL_UPDATED;

if (in_array('--delete', $argv ?? [], true)) {
    $type = IndexingNotificationType::URL_DELETED;
}

$console = GoogleConsoleFactory::fromCredentialsPath($credentials);
$result = $console->requestIndexing($url, $type);

echo "Google Indexing API - Request Indexing\n";
echo str_repeat('─', 60) . "\n\n";
echo "Request Details\n";
echo '  URL         ' . $result->url . "\n";
echo '  Type        ' . $result->type->value . "\n";
echo '  Notify Time ' . ($result->notifyTime?->format('Y-m-d H:i:s') ?? 'N/A') . "\n\n";
echo "Indexing request submitted successfully.\n";
