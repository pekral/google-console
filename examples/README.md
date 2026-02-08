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
| [inspect-url.php](inspect-url.php) | `pekral:google-url-inspect` | Inspect URL: indexing status, **business output** (primary status, confidence, reason codes), mobile usability |
| [inspect-url-business-model.php](inspect-url-business-model.php) | — | Programmatic: call `inspectUrl()` and print the **indexing check result** (primary status, confidence, reason_codes, checked_at, source_type) |
| [request-indexing.php](request-indexing.php) | `pekral:google-request-indexing` | Request indexing for a chosen URL |

Shared setup (credentials, command registration) is in [bootstrap.php](bootstrap.php).

### URL inspection and business output model

The URL inspection command and API response include an optional **business output model** (`IndexingCheckResult`) when index status data is available. It provides a normalized result with:

- **Primary status:** `INDEXED` \| `NOT_INDEXED` \| `UNKNOWN`
- **Confidence:** `high` \| `medium` \| `low`
- **Reason codes:** machine-readable list (e.g. `INDEXED_CONFIRMED`, `ROBOTS_BLOCKED`, `META_NOINDEX`)
- **Checked at:** timestamp of evaluation
- **Source type:** `authoritative` \| `heuristic`

Use [inspect-url.php](inspect-url.php) to see it in the CLI output (section "Business output (indexing check)"). Use [inspect-url-business-model.php](inspect-url-business-model.php) to access it in your own code via `$result->indexingCheckResult`.

## Direct CLI invocation

You can also run commands directly from the project root:

```bash
php bin/pekral-google <command> [arguments] [options]
```

Common options: `-c, --credentials=<path>`, `-j, --json`.
