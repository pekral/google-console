<?php

declare(strict_types = 1);

/**
 * Example: get details for a specific site from Search Console.
 * Test domain: pekral.cz
 *
 * Run: GOOGLE_CREDENTIALS_PATH=/path/to/credentials.json php examples/get-site.php
 */

require __DIR__ . '/bootstrap.php';

use Pekral\GoogleConsole\Factory\GoogleConsoleFactory;

$console = GoogleConsoleFactory::fromCredentialsPath($credentials);
$site = $console->getSite('sc-domain:pekral.cz');

echo "Google Search Console - Site Details\n";
echo str_repeat('─', 60) . "\n\n";
echo "Site Information\n";
echo '  Site URL      ' . $site->siteUrl . "\n";
echo '  Permission    ' . $site->permissionLevel . "\n";
echo '  Is Owner      ' . ($site->isOwner() ? 'Yes' : 'No') . "\n";
echo '  Full Access   ' . ($site->hasFullAccess() ? 'Yes' : 'No') . "\n";
