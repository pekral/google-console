<?php

declare(strict_types = 1);

/**
 * Example: list all sites in Search Console.
 * Test domain: pekral.cz
 *
 * Run: GOOGLE_CREDENTIALS_PATH=/path/to/credentials.json php examples/list-sites.php
 */

require __DIR__ . '/bootstrap.php';

use Pekral\GoogleConsole\Factory\GoogleConsoleFactory;

$console = GoogleConsoleFactory::fromCredentialsPath($credentials);
$sites = $console->getSiteList();

echo "Google Search Console - Sites\n";
echo str_repeat('─', 60) . "\n\n";

if ($sites === []) {
    echo "No sites found.\n";
    exit(0);
}

echo "Registered Sites\n\n";
printf("%-25s %-18s %-8s %-12s\n", 'Site URL', 'Permission', 'Owner', 'Full Access');
echo str_repeat('-', 65) . "\n";

foreach ($sites as $site) {
    printf(
        "%-25s %-18s %-8s %-12s\n",
        $site->siteUrl,
        $site->permissionLevel,
        $site->isOwner() ? 'Yes' : 'No',
        $site->hasFullAccess() ? 'Yes' : 'No',
    );
}

echo "\nFound " . count($sites) . " site(s).\n";
