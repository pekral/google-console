# Google Search Console PHP Wrapper

A modern PHP wrapper for the Google Search Console API, providing typed DTOs, clean interfaces, and ready-to-use CLI commands.

## Why This Package?

- **Simple Authentication** - Just provide the path to your credentials file
- **Typed DTOs** - All API responses are mapped to PHP objects with full type safety
- **CLI Commands** - Ready-to-use Symfony Console commands for quick access
- **Clean Architecture** - Separated concerns with factories, builders, and validators

## Who Is It For?

- SEO specialists and marketers automating their workflows
- Developers building SEO monitoring tools
- DevOps teams tracking indexation status

## Features

- **URL Inspection** – Index status, verdict, coverage state, mobile usability; optional **business output model** (primary status INDEXED/NOT_INDEXED/UNKNOWN, confidence, reason codes, **human-readable recommendations** derived from reason codes, source type authoritative/heuristic)
- **Batch URL Inspection** – Inspect multiple URLs in one call; get per-URL results, aggregation (INDEXED/NOT_INDEXED/UNKNOWN counts, reason code overview), and optional **critical URLs** with batch verdict (FAIL if any critical URL is NOT_INDEXED). Configurable batch size limits, cooldown with retries for temporary errors, and hard/soft failure distinction
- **Indexing run comparison** – Compare two indexing runs (e.g. previous vs current); get list of changes (NEWLY_INDEXED, DROPPED_FROM_INDEX, BECAME_UNKNOWN, RECOVERED_FROM_UNKNOWN), delta counts by status, and dominant reason codes from the current run
- **Operating mode** – `strict` (default: never INDEXED high without authoritative data) or `best-effort` (allows heuristic INDEXED with HEURISTIC_ONLY when inconclusive)
- **URL normalization** – Optional normalizer for API calls: remove fragment, trailing slash (preserve/add/remove), strip `utm_*` and `gclid`. Configurable via `UrlNormalizationRules`; use normalized URLs for `inspectUrl` and `requestIndexing` (e.g. batch comparison and deduplication)
- **Request Indexing** – Submit URL notifications (URL_UPDATED / URL_DELETED) via the Google Indexing API

## Installation

```bash
composer require pekral/google-console
```

## Requirements

- PHP 8.4+
- Google Service Account with access to Search Console API

## Quick Start

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

### URL Inspection

Inspects a specific URL to check its indexing status and mobile usability.

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

    foreach ($check->recommendations as $rec) {
        echo 'Recommendation: ' . $rec . PHP_EOL;  // actionable steps from reason codes
    }
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

### Batch Configuration (Limits, Cooldown, Failure Handling)

Pass a `BatchConfig` to `GoogleConsole` to enable:
- **Batch size limits** – reject batches that exceed a configurable maximum (hard failure)
- **Cooldown with retries** – automatically wait and retry on temporary API errors (rate limit, timeout, server error)
- **Soft failure handling** – record unreachable URLs as `UNKNOWN` with a reason code instead of throwing

```php
use Pekral\GoogleConsole\Config\BatchConfig;
use Pekral\GoogleConsole\GoogleConsole;

$console = new GoogleConsole($client, batchConfig: new BatchConfig(
    maxBatchSize: 50,       // max URLs per batch (default: 100)
    cooldownSeconds: 10,    // wait between retries (default: 5)
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

### URL normalization (optional)

To normalize URLs before API calls (remove fragment, strip `utm_*`/`gclid`, optional trailing slash rules), pass an `UrlNormalizer` when creating the console:

```php
use Pekral\GoogleConsole\GoogleConsole;
use Pekral\GoogleConsole\UrlNormalizer\UrlNormalizer;
use Pekral\GoogleConsole\UrlNormalizer\UrlNormalizationRules;

$normalizer = new UrlNormalizer(UrlNormalizationRules::forApiCalls());
$console = new GoogleConsole($client, urlNormalizer: $normalizer);

// inspectUrl() and requestIndexing() will receive normalized URLs
$console->inspectUrl('https://example.com/', 'https://example.com/page?utm_source=google#section');
// API is called with: https://example.com/page
```

Use `UrlNormalizationRules::defaults()` for fragment-only removal, or construct custom rules (trailing slash: `preserve` / `add` / `remove`; `stripUtmParams`, `stripGclid`).

## CLI Commands

The package includes ready-to-use Symfony Console commands:

```bash
# List all registered sites
bin/pekral-google list-sites --credentials=/path/to/credentials.json

# Get information about a specific site
bin/pekral-google get-site https://example.com/ --credentials=/path/to/credentials.json

# Get search analytics data
bin/pekral-google search-analytics https://example.com/ --credentials=/path/to/credentials.json --days=30

# Inspect a URL (default: strict mode)
bin/pekral-google inspect-url https://example.com/ https://example.com/page --credentials=/path/to/credentials.json

# Inspect with best-effort mode (allows heuristic INDEXED when data is inconclusive)
bin/pekral-google inspect-url https://example.com/ https://example.com/page --credentials=/path/to/credentials.json --mode=best-effort

# JSON output
bin/pekral-google inspect-url https://example.com/ https://example.com/page --credentials=/path/to/credentials.json --json

# Request indexing for a URL
bin/pekral-google request-indexing https://example.com/new-page --credentials=/path/to/credentials.json

# Request URL removal from the index
bin/pekral-google request-indexing https://example.com/removed-page --credentials=/path/to/credentials.json --delete
```

**Batch URL inspection** and **indexing run comparison** (programmatic examples, no CLI command):

```bash
php examples/inspect-batch-urls.php
php examples/inspect-batch-urls.php --critical=https://example.com/,https://example.com/key-page
php examples/compare-indexing-runs.php
```

## Error Handling

All API errors are wrapped in `GoogleConsoleFailure` exception with detailed context:

```php
use Pekral\GoogleConsole\Exception\GoogleConsoleFailure;

try {
    $site = $console->getSite('https://nonexistent.com/');
} catch (GoogleConsoleFailure $e) {
    echo $e->getMessage();
    // Output: Failed to get site 'https://nonexistent.com/': Site not found (reason: notFound)
}
```

### Batch Size Limit

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

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable the **Search Console API**
4. Create a **Service Account** under "IAM & Admin" > "Service Accounts"
5. Download the JSON credentials file
6. In Google Search Console, add the service account email as a user with appropriate permissions

## License

MIT

## Author

[Petr Král](https://github.com/pekral)
