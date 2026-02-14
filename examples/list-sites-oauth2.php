<?php

declare(strict_types = 1);

/**
 * Example: list all sites using OAuth2 (refresh token).
 * Use when the end user has authorized your app via OAuth2 and you stored the refresh token.
 *
 * Run:
 *   GOOGLE_OAUTH2_CREDENTIALS_PATH=/path/to/client_secret_*.json \
 *   GOOGLE_REFRESH_TOKEN=your_refresh_token \
 *   php examples/list-sites-oauth2.php
 */

require __DIR__ . '/bootstrap.php';

use Pekral\GoogleConsole\Factory\GoogleConsoleFactory;

$oauth2Path = getenv('GOOGLE_OAUTH2_CREDENTIALS_PATH') ?: '';
$refreshToken = getenv('GOOGLE_REFRESH_TOKEN') ?: '';

if ($oauth2Path === '' || !is_file($oauth2Path)) {
    echo "Error: OAuth2 credentials file not found.\n";
    echo "Set GOOGLE_OAUTH2_CREDENTIALS_PATH to the path of your client_secret_*.json\n";
    echo "(Google Cloud Console > APIs & Services > Credentials > OAuth 2.0 Client IDs).\n";
    exit(1);
}

if ($refreshToken === '') {
    echo "Error: Refresh token not set.\n";
    echo "Set GOOGLE_REFRESH_TOKEN. Obtain it via the OAuth2 authorization code flow:\n";
    echo "user consents, then exchange the code for tokens and store refresh_token.\n";
    exit(1);
}

$console = GoogleConsoleFactory::fromOAuth2RefreshToken($oauth2Path, $refreshToken);
$sites = $console->getSiteList();

echo "Google Search Console - Sites (OAuth2)\n";
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
