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
| [inspect-url.php](inspect-url.php) | `pekral:google-url-inspect` | Check URL indexing and mobile usability |
| [request-indexing.php](request-indexing.php) | `pekral:google-request-indexing` | Request indexing for a chosen URL |

Shared setup (credentials, command registration) is in [bootstrap.php](bootstrap.php).

## Direct CLI invocation

You can also run commands directly from the project root:

```bash
php bin/pekral-google <command> [arguments] [options]
```

Common options: `-c, --credentials=<path>`, `-j, --json`.
