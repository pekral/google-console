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
    inspectionUrl: 'https://example.com/article'
);

echo 'Coverage State: ' . $inspection->coverageState . PHP_EOL;
echo 'Indexing State: ' . $inspection->indexingState . PHP_EOL;
echo 'Mobile Usability: ' . $inspection->mobileUsability . PHP_EOL;
```

## CLI Commands

The package includes ready-to-use Symfony Console commands:

```bash
# List all registered sites
bin/pekral-google list-sites --credentials=/path/to/credentials.json

# Get information about a specific site
bin/pekral-google get-site https://example.com/ --credentials=/path/to/credentials.json

# Get search analytics data
bin/pekral-google search-analytics https://example.com/ --credentials=/path/to/credentials.json --days=30

# Inspect a URL
bin/pekral-google inspect-url https://example.com/ https://example.com/page --credentials=/path/to/credentials.json
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
