# CLI usage examples

Test domain used in examples: **pekral.cz**.

Before running, set the environment variable with the path to your credentials (Google Service Account JSON):

```bash
export GOOGLE_CREDENTIALS_PATH=/path/to/credentials.json
```

Run from the project root:

```bash
php examples/<name>.php
```

## Example overview (one file = one command)

| File | Command | Description |
|------|---------|-------------|
| [list-sites.php](list-sites.php) | `pekral:google-sites-list` | List all sites from Search Console |
| [get-site.php](get-site.php) | `pekral:google-site-get` | Get site details for https://pekral.cz/ |
| [search-analytics.php](search-analytics.php) | `pekral:google-analytics-search` | Search analytics (30 days, query dimension) |
| [inspect-url.php](inspect-url.php) | `pekral:google-url-inspect` | Inspect URL: indexing status, **business output** (primary status, confidence, reason codes, **recommendations**), mobile usability. Option: `--mode=strict` \| `--mode=best-effort` |
| [inspect-url-business-model.php](inspect-url-business-model.php) | — | Programmatic: call `inspectUrl()` with **URL normalizer** and print the **indexing check result** (primary status, confidence, reason_codes, **recommendations**, checked_at, source_type). Uses `UrlNormalizationRules::forApiCalls()`. |
| [inspect-url-recommendations.php](inspect-url-recommendations.php) | — | Programmatic: call `inspectUrl()` and print **human-readable recommendations** derived from reason codes (e.g. meta noindex, robots.txt, indexing in GSC). Use for reports or dashboards. |
| [inspect-batch-urls.php](inspect-batch-urls.php) | — | Programmatic: call `inspectBatchUrls()` with a list of URLs and optional **critical URLs**. Prints per-URL results, aggregation (indexed/not indexed/unknown counts, reason code overview), critical URL statuses, and batch verdict (PASS/FAIL). Option: `--critical=url1,url2` |
| [compare-indexing-runs.php](compare-indexing-runs.php) | — | Programmatic: run `inspectBatchUrls()` twice and call `compareIndexingRuns()`. Prints changes (NEWLY_INDEXED, DROPPED_FROM_INDEX, BECAME_UNKNOWN, RECOVERED_FROM_UNKNOWN), deltas by status, and dominant reason codes. For monitoring: store first run and compare with a later run. |
| [request-indexing.php](request-indexing.php) | `pekral:google-request-indexing` | Request indexing for a chosen URL |
| [url-normalization.php](url-normalization.php) | — | **URL normalization**: standalone demo of `UrlNormalizer` (defaults, forApiCalls, custom trailing slash). Optional `--api` to call `inspectUrl()` with normalizer (requires credentials). |

Shared setup (credentials, command registration) is in [bootstrap.php](bootstrap.php).

### URL inspection and business output model

The URL inspection command and API response include an optional **business output model** (`IndexingCheckResult`) when index status data is available. It provides a normalized result with:

- **Primary status:** `INDEXED` \| `NOT_INDEXED` \| `UNKNOWN`
- **Confidence:** `high` \| `medium` \| `low`
- **Reason codes:** machine-readable list (e.g. `INDEXED_CONFIRMED`, `ROBOTS_BLOCKED`, `META_NOINDEX`)
- **Recommendations:** human-readable, actionable suggestions derived from reason codes (e.g. “Remove meta noindex…”, “Allow crawling in robots.txt…”); especially useful for NOT_INDEXED/UNKNOWN results
- **Checked at:** timestamp of evaluation
- **Source type:** `authoritative` \| `heuristic`

**Operating mode:** Use `--mode=strict` (default; never INDEXED high without authoritative data) or `--mode=best-effort` (allows heuristic INDEXED with `HEURISTIC_ONLY` when data is inconclusive). When using the API, pass `OperatingMode::STRICT` or `OperatingMode::BEST_EFFORT` as the third argument to `inspectUrl()`.

Use [inspect-url.php](inspect-url.php) to see it in the CLI output (section "Business output (indexing check)"). Use [inspect-url-business-model.php](inspect-url-business-model.php) to access it in your own code via `$result->indexingCheckResult` (with URL normalizer applied before the API call). Use [inspect-url-recommendations.php](inspect-url-recommendations.php) to focus on **recommendations** (actionable steps from reason codes) for reports or dashboards.

### Batch URL inspection

[inspect-batch-urls.php](inspect-batch-urls.php) demonstrates `inspectBatchUrls()`: pass a list of URLs and optionally mark some as critical. The batch verdict is **FAIL** if any critical URL is NOT_INDEXED. Run `php examples/inspect-batch-urls.php` or add `--critical=https://example.com/,https://example.com/key` to test critical URL handling.

### Indexing run comparison

[compare-indexing-runs.php](compare-indexing-runs.php) demonstrates `compareIndexingRuns()`: run two batch inspections (e.g. previous vs current) and get a list of changes (NEWLY_INDEXED, DROPPED_FROM_INDEX, BECAME_UNKNOWN, RECOVERED_FROM_UNKNOWN), delta counts by status, and dominant reason codes. For real monitoring, store the first run and compare with a later run. Run `php examples/compare-indexing-runs.php`.

### URL normalization

[url-normalization.php](url-normalization.php) demonstrates `UrlNormalizer` and `UrlNormalizationRules`: fragment removal, trailing slash (preserve/add/remove), stripping `utm_*` and `gclid`. Run `php examples/url-normalization.php` for the standalone demo; add `--api` and set `GOOGLE_CREDENTIALS_PATH` to call `inspectUrl()` with the normalizer.

## Direct CLI invocation

You can also run commands directly from the project root:

```bash
php bin/pekral-google <command> [arguments] [options]
```

Common options: `-c, --credentials=<path>`, `-j, --json`.
