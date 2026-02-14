# Google Search Console PHP Wrapper

A modern PHP wrapper for the Google Search Console API, providing typed DTOs and clean interfaces.

## Why This Package?

- **Simple Authentication** — Service account (JSON path) or OAuth2 with refresh token
- **Typed DTOs** - All API responses are mapped to PHP objects with full type safety
- **Clean Architecture** - Separated concerns with factories, builders, and validators

## Who Is It For?

- SEO specialists and marketers automating their workflows
- Developers building SEO monitoring tools
- DevOps teams tracking indexation status

## Contents

- [Features](#features) · [Installation](#installation) · [Quick Start](#quick-start) · [Available Methods](#available-methods) · [Examples](#examples) · [Error Handling](#error-handling) · [Setting Up Google Credentials](#setting-up-google-credentials)

## Features

- **URL Inspection** — Full inspection result: index status, coverage state, mobile usability, canonicals. Returns `UrlInspectionResult`.
- **Index Status Checker** — Simple status check: `checkIndexStatus()` returns `IndexStatusCheckResult` (status, reason codes, confidence). Use for monitoring; use `inspectUrl()` for full inspection data.
- **Batch URL Inspection** — Inspect many URLs at once; per-URL results, aggregation (INDEXED/NOT_INDEXED/UNKNOWN counts), optional critical URLs and batch verdict (FAIL if any critical URL is NOT_INDEXED). Configurable batch limits, cooldown and retries for temporary errors.
- **Indexing run comparison** — Compare two runs (e.g. before/after): list of changes (NEWLY_INDEXED, DROPPED_FROM_INDEX, …), deltas by status, and reason code overview.
- **Operating mode** — `strict` (default) or `best-effort` (allows heuristic INDEXED when data is inconclusive).
- **URL normalization** — Optional normalizer before API calls: remove fragment, trailing slash, `utm_*` and `gclid`. Configure via `UrlNormalizationRules`.
- **Request Indexing** — Submit URL_UPDATED or URL_DELETED notifications via the Google Indexing API.

## Installation

```bash
composer require pekral/google-console
```

## Requirements

- PHP 8.4+
- **Service account:** Google Service Account with access to Search Console API (add the service account email as a user in Search Console), or
- **OAuth2:** OAuth2 client credentials (Web application or Desktop) and a refresh token obtained via the authorization code flow

## Quick Start

**Service Account (recommended for server-to-server):**

```php
use Pekral\GoogleConsole\GoogleConsole;

// Create instance from credentials file
$console = GoogleConsole::fromCredentialsPath('/path/to/credentials.json');

// List all registered sites
$sites = $console->getSiteList();

foreach ($sites as $site) {
    echo $site->siteUrl . ' - ' . $site->permissionLevel . PHP_EOL;
}
```

**OAuth2 with refresh token (when the end user authorizes your app):**

```php
use Pekral\GoogleConsole\GoogleConsole;

// Create instance from OAuth2 client credentials and stored refresh token
$console = GoogleConsole::fromOAuth2RefreshToken(
    '/path/to/client_secret_*.json',
    'user_refresh_token_from_authorization_code_flow',
);

$sites = $console->getSiteList();
```

## Available Methods

### Get Site List

Retrieves all sites registered in Google Search Console for the authenticated account.

```php
$sites = $console->getSiteList();
// Returns: array<Site>
```

### Get Site Information

Retrieves information about a specific site.

```php
$site = $console->getSite('https://example.com/');
// Returns: Site with siteUrl and permissionLevel
```

### Sitemaps

List, get, submit, or delete sitemaps for a site. Submit and delete require the full `webmasters` scope (the default client includes it).

**List sitemaps** (optionally filtered by sitemap index URL):

```php
$sitemaps = $console->getSitemaps('https://example.com/');
// With filter: $console->getSitemaps('https://example.com/', 'https://example.com/sitemap_index.xml');

foreach ($sitemaps as $sitemap) {
    echo $sitemap->path . ' | ' . $sitemap->type . ' | errors: ' . $sitemap->errors . "\n";
}
```

**Get a single sitemap:**

```php
$sitemap = $console->getSitemap('https://example.com/', 'https://example.com/sitemap.xml');
// Returns: Sitemap DTO (path, lastSubmitted, lastDownloaded, errors, warnings, isPending, isSitemapsIndex, type, contents)
```

**Submit a sitemap:**

```php
$console->submitSitemap('https://example.com/', 'https://example.com/sitemap.xml');
```

**Delete a sitemap:**

```php
$console->deleteSitemap('https://example.com/', 'https://example.com/sitemap.xml');
```

The `Sitemap` DTO has: `path`, `lastSubmitted`, `lastDownloaded` (nullable `DateTimeImmutable`), `errors`, `warnings`, `isPending`, `isSitemapsIndex`, `type`, and `contents` (array of `SitemapContentEntry` with `type` and `submitted` count).

### Search Analytics

Retrieves search analytics data for a specific site within a date range.

```php
$analytics = $console->getSearchAnalytics(
    siteUrl: 'https://example.com/',
    startDate: new DateTime('-30 days'),
    endDate: new DateTime('today'),
    dimensions: ['query', 'page'],
    rowLimit: 1000,
    startRow: 0
);

foreach ($analytics as $row) {
    echo sprintf(
        "Query: %s | Clicks: %d | Impressions: %d | CTR: %.2f%% | Position: %.1f\n",
        $row->query,
        $row->clicks,
        $row->impressions,
        $row->ctr * 100,
        $row->position
    );
}
```

**Available dimensions:** `query`, `page`, `country`, `device`, `searchAppearance`, `date`

### Index Status Checker (recommended for status-only checks)

Checks index status of a URL and returns a business DTO with status and reason codes. Use when you only need indexing status (e.g. monitoring, health checks). For full inspection data (mobile usability, canonicals, coverage state) use `inspectUrl()`.

```php
$result = $console->checkIndexStatus(
    siteUrl: 'https://example.com/',
    url: 'https://example.com/article',
    // operatingMode: null = OperatingMode::STRICT (default), or OperatingMode::BEST_EFFORT
    // context: optional InspectionContext (site URL, URL normalizer, mode)
);

echo $result->url . ' => ' . $result->status->value . PHP_EOL;  // INDEXED | NOT_INDEXED | UNKNOWN
echo 'Reason codes: ' . implode(', ', array_map(fn ($c) => $c->value, $result->reasonCodes)) . PHP_EOL;
echo 'Confidence: ' . $result->confidence->value . PHP_EOL;
echo 'Checked at: ' . $result->checkedAt->format('c') . PHP_EOL;
```

### URL Inspection (full result)

Inspects a specific URL and returns the full API result: indexing status, coverage state, mobile usability, canonicals. Use when you need all inspection fields; for status-only use `checkIndexStatus()`.

```php
$inspection = $console->inspectUrl(
    siteUrl: 'https://example.com/',
    inspectionUrl: 'https://example.com/article',
    // operatingMode: null = OperatingMode::STRICT (default), or OperatingMode::BEST_EFFORT
);

echo 'Coverage State: ' . $inspection->coverageState . PHP_EOL;
echo 'Indexing State: ' . $inspection->indexingState . PHP_EOL;
echo 'Mobile Friendly: ' . ($inspection->isMobileFriendly ? 'Yes' : 'No') . PHP_EOL;

// Business output (when index status data is available)
if ($inspection->indexingCheckResult !== null) {
    $check = $inspection->indexingCheckResult;
    echo 'Primary status: ' . $check->primaryStatus->value . PHP_EOL;  // INDEXED | NOT_INDEXED | UNKNOWN
    echo 'Confidence: ' . $check->confidence->value . PHP_EOL;
    echo 'Source type: ' . $check->sourceType->value . PHP_EOL;  // authoritative | heuristic
}
```

### Batch URL Inspection

Inspects multiple URLs and returns per-URL results plus aggregation. You can mark URLs as **critical**; the batch verdict is **FAIL** if any critical URL is NOT_INDEXED.

```php
$result = $console->inspectBatchUrls(
    siteUrl: 'https://example.com/',
    urls: [
        'https://example.com/',
        'https://example.com/important-page',
        'https://example.com/blog',
    ],
    criticalUrls: [
        'https://example.com/',
        'https://example.com/important-page',
    ],
    operatingMode: null  // optional: OperatingMode::STRICT or OperatingMode::BEST_EFFORT
);

echo 'Batch verdict: ' . $result->batchVerdict->value . PHP_EOL;  // PASS | FAIL
echo 'Indexed: ' . $result->aggregation->indexedCount . PHP_EOL;
echo 'Not indexed: ' . $result->aggregation->notIndexedCount . PHP_EOL;
echo 'Unknown: ' . $result->aggregation->unknownCount . PHP_EOL;

foreach ($result->perUrlResults as $url => $perUrl) {
    echo $url . ' => ' . $perUrl->status->value . PHP_EOL;  // INDEXED | NOT_INDEXED | UNKNOWN
}

foreach ($result->criticalUrlResults as $perUrl) {
    echo 'Critical: ' . $perUrl->url . ' => ' . $perUrl->status->value . PHP_EOL;
}
```

For large URL sets, consider chunking or running in a background job to avoid timeouts and API rate limits.

### Batch configuration (limits, cooldown, failures)

Pass a `BatchConfig` into `GoogleConsole` to enable:

- **Batch size limits** — Reject batches over a configurable maximum (hard failure).
- **Cooldown with retries** — Exponential backoff with jitter on temporary errors (rate limit, timeout, server error).
- **Soft failure handling** — Record unreachable URLs as `UNKNOWN` with a reason code instead of throwing.

```php
use Pekral\GoogleConsole\Config\BatchConfig;
use Pekral\GoogleConsole\GoogleConsole;

$console = new GoogleConsole($client, batchConfig: new BatchConfig(
    maxBatchSize: 50,       // max URLs per batch (default: 100)
    cooldownSeconds: 10,    // base delay for exponential backoff with jitter (default: 5)
    maxRetries: 3,          // retry attempts for soft failures (default: 2)
));

// Hard failure: batch exceeds maxBatchSize → throws BatchSizeLimitExceeded
// Soft failure: rate limit / timeout / server error → recorded as UNKNOWN

$result = $console->inspectBatchUrls('https://example.com/', $urls);

foreach ($result->perUrlResults as $url => $perUrl) {
    if ($perUrl->isSoftFailure()) {
        echo $url . ' => soft failure (reason: '
            . $perUrl->result->indexingCheckResult?->reasonCodes[0]->value . ')' . PHP_EOL;
    } else {
        echo $url . ' => ' . $perUrl->status->value . PHP_EOL;
    }
}
```

**Without `BatchConfig`**, the library behaves exactly as before (no limits, no retries, exceptions propagate).

**Failure types:**
| HTTP Code | Type | Reason Code | Behavior |
|-----------|------|-------------|----------|
| 429 | Soft | `RATE_LIMITED` | Cooldown + retry, then record as UNKNOWN |
| 408, 504 | Soft | `TIMEOUT` | Cooldown + retry, then record as UNKNOWN |
| 500, 502, 503 | Soft | `INSUFFICIENT_DATA` | Cooldown + retry, then record as UNKNOWN |
| 400, 403, 404, … | Hard | — | Exception thrown immediately |

### Compare Indexing Runs

Compares two indexing runs (e.g. previous vs current) and returns changes, deltas and dominant reason codes. Only URLs present in **both** runs are compared.

```php
$previous = $console->inspectBatchUrls($siteUrl, $urls, $criticalUrls);
// ... later, after some time or after changes ...
$current = $console->inspectBatchUrls($siteUrl, $urls, $criticalUrls);

$comparison = $console->compareIndexingRuns($previous, $current);

echo 'Changes: ' . count($comparison->changes) . PHP_EOL;
echo 'Indexed delta: ' . $comparison->indexedDelta . PHP_EOL;
echo 'Not indexed delta: ' . $comparison->notIndexedDelta . PHP_EOL;
echo 'Unknown delta: ' . $comparison->unknownDelta . PHP_EOL;

foreach ($comparison->changes as $change) {
    echo $change->url . ' => ' . $change->changeType->value;
    echo ' (' . $change->previousStatus->value . ' -> ' . $change->currentStatus->value . ')' . PHP_EOL;
}

foreach ($comparison->dominantReasonCodes as $code => $count) {
    echo '  ' . $code . ': ' . $count . PHP_EOL;
}
```

Change types: `NEWLY_INDEXED`, `DROPPED_FROM_INDEX`, `BECAME_UNKNOWN`, `RECOVERED_FROM_UNKNOWN`.

### Request Indexing

Requests indexing (or removal) for a URL via the Google Indexing API.

```php
use Pekral\GoogleConsole\Enum\IndexingNotificationType;

$result = $console->requestIndexing(
    url: 'https://example.com/new-article',
    type: IndexingNotificationType::URL_UPDATED  // or URL_DELETED
);

echo 'URL: ' . $result->url . PHP_EOL;
echo 'Type: ' . $result->type->value . PHP_EOL;
echo 'Notify time: ' . ($result->notifyTime?->format('c') ?? 'N/A') . PHP_EOL;
```

**Index management workflow:** The [Indexing API quickstart](https://developers.google.com/search/apis/indexing-api/v3/quickstart) recommends using a **sitemap** for full website coverage and reserving the Indexing API for specific short-lived page types (e.g. job postings, livestreams). For pages that do not qualify for the Indexing API, submitting or updating your sitemap in Search Console is the standard way to notify Google about new or changed URLs.

### URL normalization (optional)

To normalize URLs before API calls (remove fragment, strip `utm_*`/`gclid`, optional trailing slash rules), pass an `UrlNormalizer` when creating the console:

```php
use Pekral\GoogleConsole\GoogleConsole;
use Pekral\GoogleConsole\UrlNormalizer\UrlNormalizer;
use Pekral\GoogleConsole\UrlNormalizer\UrlNormalizationRules;

$normalizer = new UrlNormalizer(UrlNormalizationRules::forApiCalls());
$console = new GoogleConsole($client, urlNormalizer: $normalizer);

// inspectUrl(), checkIndexStatus(), and requestIndexing() will receive normalized URLs
$console->checkIndexStatus('https://example.com/', 'https://example.com/page?utm_source=google#section');
// API is called with: https://example.com/page
```

Use `UrlNormalizationRules::defaults()` for fragment-only removal, or construct custom rules (trailing slash: `preserve` / `add` / `remove`; `stripUtmParams`, `stripGclid`).

### API overview: Index Status Checker vs URL Inspection

| Use case | Method | Return type |
|----------|--------|-------------|
| Status-only (monitoring, health checks) | `checkIndexStatus(siteUrl, url, context?)` | `IndexStatusCheckResult` (status, reason_codes, confidence, checked_at, source_type, url) |
| Full inspection (mobile, canonicals, coverage state) | `inspectUrl(siteUrl, inspectionUrl, operatingMode?, context?)` | `UrlInspectionResult` (raw API fields + optional `indexingCheckResult`) |

`inspectUrl` remains the low-level call; it is not deprecated. Prefer `checkIndexStatus()` when you only need indexing status and reason codes.

## Examples

Run the example scripts. For service account, set `GOOGLE_CREDENTIALS_PATH`. For OAuth2, set `GOOGLE_OAUTH2_CREDENTIALS_PATH` and `GOOGLE_REFRESH_TOKEN` (see [examples/README.md](examples/README.md)).

```bash
php examples/list-sites.php
php examples/list-sites-oauth2.php   # OAuth2: needs GOOGLE_OAUTH2_CREDENTIALS_PATH + GOOGLE_REFRESH_TOKEN
php examples/get-site.php
php examples/search-analytics.php
php examples/inspect-url.php
php examples/inspect-url.php --mode=best-effort
php examples/inspect-url.php --json
php examples/request-indexing.php
php examples/inspect-batch-urls.php
php examples/inspect-batch-urls.php --critical=https://example.com/,https://example.com/key-page
php examples/compare-indexing-runs.php
```

See [examples/README.md](examples/README.md) for a full overview.

## Error Handling

### API errors

All API errors are wrapped in `GoogleConsoleFailure` with detailed context:

```php
use Pekral\GoogleConsole\Exception\GoogleConsoleFailure;

try {
    $site = $console->getSite('https://nonexistent.com/');
} catch (GoogleConsoleFailure $e) {
    echo $e->getMessage();
    // Output: Failed to get site 'https://nonexistent.com/': Site not found (reason: notFound)
}
```

### Quota and rate limiting

When the optional `RateLimiter` is used (e.g. `new GoogleConsole($client, rateLimiter: new RateLimiter())`), the library enforces QPD/QPM per API family. If a limit is exceeded, it throws `QuotaExceededException` (extends `GoogleConsoleFailure`). You can use `$e->getRetryAfterSeconds()` for retry logic. Without a rate limiter, the library does not enforce quotas; Google may return HTTP 429, which is then wrapped in `GoogleConsoleFailure`.

### Batch size limit

When using `BatchConfig`, exceeding the batch size throws `BatchSizeLimitExceeded`:

```php
use Pekral\GoogleConsole\Exception\BatchSizeLimitExceeded;

try {
    $result = $console->inspectBatchUrls('https://example.com/', $tooManyUrls);
} catch (BatchSizeLimitExceeded $e) {
    echo $e->getMessage();
    // Output: Batch size 150 exceeds maximum of 100 URLs
}
```

## Setting Up Google Credentials

### Service Account

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable the **Search Console API**
4. Create a **Service Account** under "IAM & Admin" > "Service Accounts"
5. Download the JSON credentials file
6. In Google Search Console, add the service account email as a user with appropriate permissions

### OAuth2 (user consent)

1. In Google Cloud Console, create **OAuth 2.0 Client ID** credentials (Web application or Desktop).
2. Enable the **Search Console API** and request scopes: `https://www.googleapis.com/auth/webmasters.readonly` (and indexing scope if you use the Indexing API).
3. Run the authorization code flow: redirect the user to the consent URL, then exchange the returned `code` for tokens. Store the `refresh_token`.
4. Use `GoogleConsole::fromOAuth2RefreshToken($pathToClientSecretJson, $refreshToken)` or `GoogleConsoleFactory::fromOAuth2RefreshToken(...)`. For credentials from env or secrets, build `OAuth2Config` and use `GoogleConsoleFactory::fromOAuth2Config($config)`.

## License

MIT

## Author

[Petr Král](https://github.com/pekral)
