<?php

declare(strict_types = 1);

/**
 * Example: using RateLimiter to enforce Search Console and Indexing API quotas.
 *
 * Demonstrates:
 * - Optional RateLimiter for QPD (per day) and QPM (per minute) per API family
 * - Keying: (apiFamily, siteUrl) for URL Inspection and Search Analytics; (apiFamily) for Indexing and Other
 * - QuotaExceededException when limit is reached (fail-fast)
 *
 * Run:
 *   GOOGLE_CREDENTIALS_PATH=/path/to/credentials.json php examples/rate-limiter.php
 *
 * Test domain: pekral.cz
 */

require __DIR__ . '/bootstrap.php';

use Pekral\GoogleConsole\Exception\QuotaExceededException;
use Pekral\GoogleConsole\Factory\GoogleConsoleFactory;
use Pekral\GoogleConsole\RateLimit\RateLimiter;

$rateLimiter = new RateLimiter();
$console = GoogleConsoleFactory::fromCredentialsPath($credentials, rateLimiter: $rateLimiter);

echo "Rate limiter enabled (QPD/QPM per API family)\n";
echo str_repeat('─', 60) . "\n\n";

try {
    $sites = $console->getSiteList();
    echo 'Sites listed: ' . count($sites) . "\n";
} catch (QuotaExceededException $e) {
    echo 'QUOTA EXCEEDED: ' . $e->getMessage() . "\n";

    if ($e->getRetryAfterSeconds() !== null) {
        echo 'Retry after ' . $e->getRetryAfterSeconds() . " seconds\n";
    }

    exit(1);
}
