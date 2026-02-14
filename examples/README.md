# Usage examples

Test domain used in examples: **pekral.cz**.

Before running:

- **Service account examples:** set `GOOGLE_CREDENTIALS_PATH` to your service account JSON path.
- **OAuth2 example** ([list-sites-oauth2.php](list-sites-oauth2.php)): set `GOOGLE_OAUTH2_CREDENTIALS_PATH` (path to `client_secret_*.json`) and `GOOGLE_REFRESH_TOKEN`.

```bash
export GOOGLE_CREDENTIALS_PATH=/path/to/credentials.json
# For OAuth2:
# export GOOGLE_OAUTH2_CREDENTIALS_PATH=/path/to/client_secret_xxx.json
# export GOOGLE_REFRESH_TOKEN=your_refresh_token
```

Run from the project root:

```bash
php examples/<name>.php
```

## Example overview

| File | Description |
|------|-------------|
| [list-sites.php](list-sites.php) | List all sites from Search Console (service account) |
| [list-sites-oauth2.php](list-sites-oauth2.php) | List all sites using OAuth2 (refresh token). Env: `GOOGLE_OAUTH2_CREDENTIALS_PATH`, `GOOGLE_REFRESH_TOKEN`. |
| [get-site.php](get-site.php) | Get site details for sc-domain:pekral.cz |
| [search-analytics.php](search-analytics.php) | Search analytics (30 days, query dimension) |
| [inspect-url.php](inspect-url.php) | Inspect URL: indexing status, business output (primary status, confidence, reason codes), mobile usability. Options: `--mode=strict` \| `--mode=best-effort`, `--json` |
| [check-index-status.php](check-index-status.php) | Call `checkIndexStatus()` and print IndexStatusCheckResult (url, status, reason_codes, confidence, checked_at, source_type). Use for status-only checks. |
| [inspect-url-business-model.php](inspect-url-business-model.php) | Call `inspectUrl()` with URL normalizer and print the indexing check result. Uses `UrlNormalizationRules::forApiCalls()`. |
| [inspect-batch-urls.php](inspect-batch-urls.php) | Call `inspectBatchUrls()` with a list of URLs and optional critical URLs. Option: `--critical=url1,url2` |
| [compare-indexing-runs.php](compare-indexing-runs.php) | Run `inspectBatchUrls()` twice and call `compareIndexingRuns()`. Prints changes, deltas, and dominant reason codes. |
| [request-indexing.php](request-indexing.php) | Request indexing for a URL. Option: `--delete` for URL removal. |
| [url-normalization.php](url-normalization.php) | URL normalization demo (standalone). Optional `--api` to call `inspectUrl()` with normalizer (requires credentials). |
| [batch-config.php](batch-config.php) | Batch URL inspection with BatchConfig (limits, cooldown, retries). |
| [rate-limiter.php](rate-limiter.php) | RateLimiter for QPD/QPM per API family. |

Shared setup (credentials) is in [bootstrap.php](bootstrap.php).

### URL inspection and business output model

The URL inspection API response includes an optional **business output model** (`IndexingCheckResult`) when index status data is available:

- **Primary status:** `INDEXED` \| `NOT_INDEXED` \| `UNKNOWN`
- **Confidence:** `high` \| `medium` \| `low`
- **Reason codes:** machine-readable list (e.g. `INDEXED_CONFIRMED`, `ROBOTS_BLOCKED`, `META_NOINDEX`)
- **Checked at:** timestamp of evaluation
- **Source type:** `authoritative` \| `heuristic`

**Operating mode:** Use `--mode=strict` (default) or `--mode=best-effort` in [inspect-url.php](inspect-url.php). When using the API, pass `OperatingMode::STRICT` or `OperatingMode::BEST_EFFORT` as the third argument to `inspectUrl()`.

### Batch URL inspection

[inspect-batch-urls.php](inspect-batch-urls.php) demonstrates `inspectBatchUrls()`: pass a list of URLs and optionally mark some as critical. The batch verdict is **FAIL** if any critical URL is NOT_INDEXED.

### Indexing run comparison

[compare-indexing-runs.php](compare-indexing-runs.php) demonstrates `compareIndexingRuns()`: run two batch inspections (e.g. previous vs current) and get a list of changes (NEWLY_INDEXED, DROPPED_FROM_INDEX, BECAME_UNKNOWN, RECOVERED_FROM_UNKNOWN), delta counts by status, and dominant reason codes.

### URL normalization

[url-normalization.php](url-normalization.php) demonstrates `UrlNormalizer` and `UrlNormalizationRules`: fragment removal, trailing slash (preserve/add/remove), stripping `utm_*` and `gclid`. Add `--api` and set `GOOGLE_CREDENTIALS_PATH` to call `inspectUrl()` with the normalizer.
