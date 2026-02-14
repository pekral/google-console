<?php

declare(strict_types = 1);

/**
 * Example: list sitemaps submitted for a site.
 *
 * Run: GOOGLE_CREDENTIALS_PATH=/path/to/credentials.json php examples/list-sitemaps.php
 *
 * Optional: filter by sitemap index URL
 *   php examples/list-sitemaps.php --sitemap-index=https://example.com/sitemap_index.xml
 */

require __DIR__ . '/bootstrap.php';

use Pekral\GoogleConsole\DTO\SitemapContentEntry;
use Pekral\GoogleConsole\Factory\GoogleConsoleFactory;

$siteUrl = 'sc-domain:pekral.cz';
$sitemapIndex = null;

$argv ??= [];

foreach ($argv as $i => $arg) {
    if ($arg === '--sitemap-index' && isset($argv[$i + 1])) {
        $sitemapIndex = trim($argv[$i + 1]);

        break;
    }

    if (str_starts_with($arg, '--sitemap-index=')) {
        $sitemapIndex = trim(substr($arg, 17));

        break;
    }
}

$console = GoogleConsoleFactory::fromCredentialsPath($credentials);
$sitemaps = $console->getSitemaps($siteUrl, $sitemapIndex);

echo 'Sitemaps for ' . $siteUrl . "\n";
echo str_repeat('─', 60) . "\n\n";

if ($sitemaps === []) {
    echo "No sitemaps found.\n";
    exit(0);
}

foreach ($sitemaps as $sitemap) {
    echo 'Path:    ' . $sitemap->path . "\n";
    echo 'Type:    ' . $sitemap->type . "\n";
    echo 'Pending: ' . ($sitemap->isPending ? 'Yes' : 'No') . "\n";
    echo 'Index:   ' . ($sitemap->isSitemapsIndex ? 'Yes' : 'No') . "\n";
    echo 'Errors:  ' . $sitemap->errors . ' | Warnings: ' . $sitemap->warnings . "\n";
    echo 'Last submitted:  ' . ($sitemap->lastSubmitted?->format('Y-m-d H:i') ?? 'N/A') . "\n";
    echo 'Last downloaded: ' . ($sitemap->lastDownloaded?->format('Y-m-d H:i') ?? 'N/A') . "\n";

    if ($sitemap->contents !== []) {
        echo 'Contents: ';
        $parts = array_map(
            static fn (SitemapContentEntry $c): string => $c->type . '=' . $c->submitted,
            $sitemap->contents,
        );
        echo implode(', ', $parts) . "\n";
    }

    echo "\n";
}

echo 'Total: ' . count($sitemaps) . " sitemap(s).\n";
