<?php

declare(strict_types = 1);

/**
 * Example: URL normalization for API calls and batch comparison.
 *
 * Demonstrates:
 * - UrlNormalizer with UrlNormalizationRules (defaults, forApiCalls, custom)
 * - Effect of rules: remove fragment, trailing slash (preserve/add/remove), strip utm_* and gclid
 * - Using GoogleConsole with UrlNormalizer so inspectUrl and requestIndexing receive normalized URLs
 *
 * Run (standalone normalizer only, no credentials needed for first part):
 *   php examples/url-normalization.php
 *
 * Run with API call (inspect URL using normalized URL; needs credentials):
 *   GOOGLE_CREDENTIALS_PATH=/path/to/credentials.json php examples/url-normalization.php --api
 */

require __DIR__ . '/../vendor/autoload.php';

use Pekral\GoogleConsole\Factory\GoogleConsoleFactory;
use Pekral\GoogleConsole\UrlNormalizer\TrailingSlashMode;
use Pekral\GoogleConsole\UrlNormalizer\UrlNormalizationRules;
use Pekral\GoogleConsole\UrlNormalizer\UrlNormalizer;

function runStandaloneDemo(): void
{
    echo "URL normalization (standalone)\n";
    echo str_repeat('─', 60) . "\n";

    $urls = [
        'https://example.com/page?utm_source=google&gclid=abc#section',
        'https://example.com/blog/',
        'https://example.com/docs',
    ];

    $rulesDefault = UrlNormalizationRules::defaults();
    $rulesApi = UrlNormalizationRules::forApiCalls();
    $rulesNoTrailing = new UrlNormalizationRules(removeFragment: true, trailingSlash: TrailingSlashMode::REMOVE, stripUtmParams: true, stripGclid: true);

    $normalizerDefault = new UrlNormalizer($rulesDefault);
    $normalizerApi = new UrlNormalizer($rulesApi);
    $normalizerNoTrailing = new UrlNormalizer($rulesNoTrailing);

    foreach ($urls as $url) {
        echo 'Original:     ' . $url . "\n";
        echo '  defaults:   ' . $normalizerDefault->normalize($url) . "\n";
        echo '  forApiCalls:' . $normalizerApi->normalize($url) . "\n";
        echo '  no trailing:' . $normalizerNoTrailing->normalize($url) . "\n";
        echo "\n";
    }
}

function runApiExample(string $credentialsPath): void
{
    $normalizer = new UrlNormalizer(UrlNormalizationRules::forApiCalls());
    $console = GoogleConsoleFactory::fromCredentialsPath($credentialsPath, urlNormalizer: $normalizer);

    $siteUrl = 'sc-domain:pekral.cz';
    $inspectionUrl = 'https://pekral.cz/?utm_source=test#anchor';

    echo "API call with URL normalizer\n";
    echo str_repeat('─', 60) . "\n";
    echo 'Requested inspection URL (before normalization): ' . $inspectionUrl . "\n";
    echo 'URL sent to API (normalized):                   ' . $normalizer->normalize($inspectionUrl) . "\n\n";

    $result = $console->inspectUrl($siteUrl, $inspectionUrl);

    echo "Inspection result:\n";
    echo '  Verdict:        ' . $result->verdict . "\n";
    echo '  Coverage state: ' . $result->coverageState . "\n";

    if ($result->indexingCheckResult !== null) {
        echo '  Primary status: ' . $result->indexingCheckResult->primaryStatus->value . "\n";
    }
}

$useApi = in_array('--api', $argv ?? [], true);

if ($useApi) {
    $credentials = getenv('GOOGLE_CREDENTIALS_PATH') ?: '';

    if ($credentials === '' || !is_file($credentials)) {
        echo "Error: Set GOOGLE_CREDENTIALS_PATH for --api.\n";
        exit(1);
    }

    runStandaloneDemo();
    echo "\n";
    runApiExample($credentials);
} else {
    runStandaloneDemo();
    echo "Tip: Run with --api and GOOGLE_CREDENTIALS_PATH to call inspectUrl with normalized URL.\n";
}
