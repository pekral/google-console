<?php

declare(strict_types = 1);

use Google\Client;
use Google\Service\Exception as GoogleServiceException;
use Google\Service\SearchConsole as SearchConsoleService;
use Google\Service\SearchConsole\IndexStatusInspectionResult;
use Google\Service\SearchConsole\InspectUrlIndexRequest;
use Google\Service\SearchConsole\InspectUrlIndexResponse;
use Google\Service\SearchConsole\MobileUsabilityInspectionResult;
use Google\Service\SearchConsole\UrlInspectionResult as GoogleUrlInspectionResult;
use Google\Service\Webmasters as WebmastersService;
use Google\Service\Webmasters\ApiDataRow;
use Google\Service\Webmasters\SearchAnalyticsQueryResponse;
use Google\Service\Webmasters\SitesListResponse;
use Google\Service\Webmasters\WmxSite;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Pekral\GoogleConsole\Config\Backoff;
use Pekral\GoogleConsole\Config\BatchConfig;
use Pekral\GoogleConsole\Config\GoogleConfig;
use Pekral\GoogleConsole\DTO\BatchAggregation;
use Pekral\GoogleConsole\DTO\BatchUrlInspectionResult;
use Pekral\GoogleConsole\DTO\IndexingResult;
use Pekral\GoogleConsole\DTO\IndexStatusCheckResult;
use Pekral\GoogleConsole\DTO\InspectionContext;
use Pekral\GoogleConsole\DTO\PerUrlInspectionResult;
use Pekral\GoogleConsole\DTO\SearchAnalyticsRow;
use Pekral\GoogleConsole\DTO\Site;
use Pekral\GoogleConsole\DTO\UrlInspectionResult;
use Pekral\GoogleConsole\Enum\BatchVerdict;
use Pekral\GoogleConsole\Enum\FailureType;
use Pekral\GoogleConsole\Enum\IndexingChangeType;
use Pekral\GoogleConsole\Enum\IndexingCheckReasonCode;
use Pekral\GoogleConsole\Enum\IndexingCheckSourceType;
use Pekral\GoogleConsole\Enum\IndexingCheckStatus;
use Pekral\GoogleConsole\Enum\IndexingNotificationType;
use Pekral\GoogleConsole\Exception\BatchSizeLimitExceeded;
use Pekral\GoogleConsole\Exception\GoogleConsoleFailure;
use Pekral\GoogleConsole\Factory\GoogleClientFactory;
use Pekral\GoogleConsole\GoogleConsole;
use Pekral\GoogleConsole\UrlNormalizer\UrlNormalizationRules;
use Pekral\GoogleConsole\UrlNormalizer\UrlNormalizer;

function createTestCredentialsFile(): string
{
    $tempFile = sys_get_temp_dir() . '/test-credentials-' . uniqid() . '.json';
    file_put_contents($tempFile, json_encode([
        'auth_provider_x509_cert_url' => 'https://www.googleapis.com/oauth2/v1/certs',
        'auth_uri' => 'https://accounts.google.com/o/oauth2/auth',
        'client_email' => 'test@test.iam.gserviceaccount.com',
        'client_id' => '123456789',
        'client_x509_cert_url' => 'https://www.googleapis.com/robot/v1/metadata/x509/test',
        'private_key' => "-----BEGIN RSA PRIVATE KEY-----\nMIIEowIBAAKCAQEA2a2rwplBQLFMzb8H8g==\n-----END RSA PRIVATE KEY-----\n",
        'private_key_id' => 'key-id',
        'project_id' => 'test-project',
        'token_uri' => 'https://oauth2.googleapis.com/token',
        'type' => 'service_account',
    ]));

    return $tempFile;
}

function createGoogleConsole(?BatchConfig $batchConfig = null): GoogleConsole
{
    $tempFile = createTestCredentialsFile();
    $config = GoogleConfig::fromCredentialsPath($tempFile);
    $console = new GoogleConsole(new GoogleClientFactory()->create($config), batchConfig: $batchConfig);
    unlink($tempFile);

    return $console;
}

function noOpSleep(): Closure
{
    return static function (int $seconds): void {
        assert($seconds >= 0);
    };
}

function createBatchConfig(int $maxBatchSize = 100, int $cooldownSeconds = 1, int $maxRetries = 2): BatchConfig
{
    return new BatchConfig(
        maxBatchSize: $maxBatchSize,
        cooldownSeconds: $cooldownSeconds,
        maxRetries: $maxRetries,
        sleepFunction: noOpSleep(),
    );
}

describe(GoogleConsole::class, function (): void {

    it('creates instance from credentials path', function (): void {
        $tempFile = createTestCredentialsFile();

        $console = GoogleConsole::fromCredentialsPath($tempFile);

        expect($console)->toBeInstanceOf(GoogleConsole::class)
            ->and($console->getClient())->toBeInstanceOf(Client::class);

        unlink($tempFile);
    });

    it('throws exception when credentials file not found', function (): void {
        GoogleConsole::fromCredentialsPath('/non/existent/path.json');
    })->throws(GoogleConsoleFailure::class, 'Credentials file not found');

    it('returns client instance', function (): void {
        $console = createGoogleConsole();

        expect($console->getClient())->toBeInstanceOf(Client::class);
    });

    it('throws exception for invalid dimension', function (): void {
        $console = createGoogleConsole();

        $console->getSearchAnalytics(
            'https://example.com/',
            new DateTimeImmutable('2024-01-01'),
            new DateTimeImmutable('2024-01-31'),
            ['invalid_dimension'],
        );
    })->throws(GoogleConsoleFailure::class, 'Invalid dimension "invalid_dimension"');

    it('returns site list', function (): void {
        $console = createGoogleConsole();

        $wmxSite1 = Mockery::mock(WmxSite::class);
        $wmxSite1->shouldReceive('getSiteUrl')->andReturn('https://example.com/');
        $wmxSite1->shouldReceive('getPermissionLevel')->andReturn('siteOwner');

        $wmxSite2 = Mockery::mock(WmxSite::class);
        $wmxSite2->shouldReceive('getSiteUrl')->andReturn('https://example.org/');
        $wmxSite2->shouldReceive('getPermissionLevel')->andReturn('siteFullUser');

        $sitesListResponse = Mockery::mock(SitesListResponse::class);
        $sitesListResponse->shouldReceive('getSiteEntry')->andReturn([$wmxSite1, $wmxSite2]);

        $sitesResource = Mockery::mock(WebmastersService\Resource\Sites::class);
        $sitesResource->shouldReceive('listSites')->andReturn($sitesListResponse);

        $webmastersService = Mockery::mock(WebmastersService::class);
        $webmastersService->sites = $sitesResource;

        $reflection = new ReflectionClass($console);
        $property = $reflection->getProperty('webmastersService');
        $property->setValue($console, $webmastersService);

        $sites = $console->getSiteList();

        expect($sites)->toHaveCount(2)
            ->and($sites[0])->toBeInstanceOf(Site::class)
            ->and($sites[0]->siteUrl)->toBe('https://example.com/')
            ->and($sites[0]->permissionLevel)->toBe('siteOwner')
            ->and($sites[1]->siteUrl)->toBe('https://example.org/')
            ->and($sites[1]->permissionLevel)->toBe('siteFullUser');
    });

    it('returns empty site list when no sites', function (): void {
        $console = createGoogleConsole();

        $sitesListResponse = Mockery::mock(SitesListResponse::class);
        $sitesListResponse->shouldReceive('getSiteEntry')->andReturn(null);

        $sitesResource = Mockery::mock(WebmastersService\Resource\Sites::class);
        $sitesResource->shouldReceive('listSites')->andReturn($sitesListResponse);

        $webmastersService = Mockery::mock(WebmastersService::class);
        $webmastersService->sites = $sitesResource;

        $reflection = new ReflectionClass($console);
        $property = $reflection->getProperty('webmastersService');
        $property->setValue($console, $webmastersService);

        $sites = $console->getSiteList();

        expect($sites)->toBeArray()->toBeEmpty();
    });

    it('returns single site', function (): void {
        $console = createGoogleConsole();

        $wmxSite = Mockery::mock(WmxSite::class);
        $wmxSite->shouldReceive('getSiteUrl')->andReturn('https://example.com/');
        $wmxSite->shouldReceive('getPermissionLevel')->andReturn('siteOwner');

        $sitesResource = Mockery::mock(WebmastersService\Resource\Sites::class);
        $sitesResource->shouldReceive('get')->with('https://example.com/')->andReturn($wmxSite);

        $webmastersService = Mockery::mock(WebmastersService::class);
        $webmastersService->sites = $sitesResource;

        $reflection = new ReflectionClass($console);
        $property = $reflection->getProperty('webmastersService');
        $property->setValue($console, $webmastersService);

        $site = $console->getSite('https://example.com/');

        expect($site)->toBeInstanceOf(Site::class)
            ->and($site->siteUrl)->toBe('https://example.com/')
            ->and($site->permissionLevel)->toBe('siteOwner');
    });

    it('throws exception when site not found', function (): void {
        $console = createGoogleConsole();

        $sitesResource = Mockery::mock(WebmastersService\Resource\Sites::class);
        $sitesResource->shouldReceive('get')
            ->with('https://nonexistent.com/')
            ->andThrow(new GoogleServiceException('Not found', 404));

        $webmastersService = Mockery::mock(WebmastersService::class);
        $webmastersService->sites = $sitesResource;

        $reflection = new ReflectionClass($console);
        $property = $reflection->getProperty('webmastersService');
        $property->setValue($console, $webmastersService);

        $console->getSite('https://nonexistent.com/');
    })->throws(GoogleConsoleFailure::class, 'Failed to get site \'https://nonexistent.com/\'');

    it('returns search analytics rows', function (): void {
        $console = createGoogleConsole();

        $row1 = Mockery::mock(ApiDataRow::class);
        $row1->shouldReceive('getKeys')->andReturn(['test query']);
        $row1->shouldReceive('getClicks')->andReturn(100.0);
        $row1->shouldReceive('getImpressions')->andReturn(1_000.0);
        $row1->shouldReceive('getCtr')->andReturn(0.1);
        $row1->shouldReceive('getPosition')->andReturn(5.5);

        $row2 = Mockery::mock(ApiDataRow::class);
        $row2->shouldReceive('getKeys')->andReturn(['another query']);
        $row2->shouldReceive('getClicks')->andReturn(50.0);
        $row2->shouldReceive('getImpressions')->andReturn(500.0);
        $row2->shouldReceive('getCtr')->andReturn(0.1);
        $row2->shouldReceive('getPosition')->andReturn(3.0);

        $queryResponse = Mockery::mock(SearchAnalyticsQueryResponse::class);
        $queryResponse->shouldReceive('getRows')->andReturn([$row1, $row2]);

        $searchAnalyticsResource = Mockery::mock(WebmastersService\Resource\Searchanalytics::class);
        $searchAnalyticsResource->shouldReceive('query')->andReturn($queryResponse);

        $webmastersService = Mockery::mock(WebmastersService::class);
        $webmastersService->searchanalytics = $searchAnalyticsResource;

        $reflection = new ReflectionClass($console);
        $property = $reflection->getProperty('webmastersService');
        $property->setValue($console, $webmastersService);

        $rows = $console->getSearchAnalytics(
            'https://example.com/',
            new DateTimeImmutable('2024-01-01'),
            new DateTimeImmutable('2024-01-31'),
        );

        expect($rows)->toHaveCount(2)
            ->and($rows[0])->toBeInstanceOf(SearchAnalyticsRow::class)
            ->and($rows[0]->clicks)->toBe(100.0)
            ->and($rows[1]->clicks)->toBe(50.0);
    });

    it('returns empty search analytics when no rows', function (): void {
        $console = createGoogleConsole();

        $queryResponse = Mockery::mock(SearchAnalyticsQueryResponse::class);
        $queryResponse->shouldReceive('getRows')->andReturn(null);

        $searchAnalyticsResource = Mockery::mock(WebmastersService\Resource\Searchanalytics::class);
        $searchAnalyticsResource->shouldReceive('query')->andReturn($queryResponse);

        $webmastersService = Mockery::mock(WebmastersService::class);
        $webmastersService->searchanalytics = $searchAnalyticsResource;

        $reflection = new ReflectionClass($console);
        $property = $reflection->getProperty('webmastersService');
        $property->setValue($console, $webmastersService);

        $rows = $console->getSearchAnalytics(
            'https://example.com/',
            new DateTimeImmutable('2024-01-01'),
            new DateTimeImmutable('2024-01-31'),
        );

        expect($rows)->toBeArray()->toBeEmpty();
    });

    it('inspects url successfully', function (): void {
        $console = createGoogleConsole();

        $indexStatus = Mockery::mock(IndexStatusInspectionResult::class);
        $indexStatus->shouldReceive('getVerdict')->andReturn('PASS');
        $indexStatus->shouldReceive('getCoverageState')->andReturn('Submitted and indexed');
        $indexStatus->shouldReceive('getRobotsTxtState')->andReturn('ALLOWED');
        $indexStatus->shouldReceive('getIndexingState')->andReturn('INDEXING_ALLOWED');
        $indexStatus->shouldReceive('getLastCrawlTime')->andReturn('2024-01-15T10:30:00Z');
        $indexStatus->shouldReceive('getPageFetchState')->andReturn('SUCCESSFUL');
        $indexStatus->shouldReceive('getCrawledAs')->andReturn('MOBILE');
        $indexStatus->shouldReceive('getGoogleCanonical')->andReturn('https://example.com/page');
        $indexStatus->shouldReceive('getUserCanonical')->andReturn('https://example.com/page');

        $mobileUsability = Mockery::mock(MobileUsabilityInspectionResult::class);
        $mobileUsability->shouldReceive('getVerdict')->andReturn('PASS');
        $mobileUsability->shouldReceive('getIssues')->andReturn([]);

        $inspectionResult = Mockery::mock(GoogleUrlInspectionResult::class);
        $inspectionResult->shouldReceive('getInspectionResultLink')->andReturn('https://search.google.com/search-console/inspect');
        $inspectionResult->shouldReceive('getIndexStatusResult')->andReturn($indexStatus);
        $inspectionResult->shouldReceive('getMobileUsabilityResult')->andReturn($mobileUsability);

        $inspectResponse = Mockery::mock(InspectUrlIndexResponse::class);
        $inspectResponse->shouldReceive('getInspectionResult')->andReturn($inspectionResult);

        $urlInspectionResource = Mockery::mock(SearchConsoleService\Resource\UrlInspectionIndex::class);
        $urlInspectionResource->shouldReceive('inspect')->andReturn($inspectResponse);

        $searchConsoleService = Mockery::mock(SearchConsoleService::class);
        $searchConsoleService->urlInspection_index = $urlInspectionResource;

        $reflection = new ReflectionClass($console);
        $property = $reflection->getProperty('searchConsoleService');
        $property->setValue($console, $searchConsoleService);

        $result = $console->inspectUrl('https://example.com/', 'https://example.com/page');

        expect($result)->toBeInstanceOf(UrlInspectionResult::class)
            ->and($result->verdict)->toBe('PASS')
            ->and($result->isIndexed())->toBeTrue()
            ->and($result->isMobileFriendly)->toBeTrue();
    });

    it('inspects url with null index status and mobile usability', function (): void {
        $console = createGoogleConsole();

        $inspectionResult = Mockery::mock(GoogleUrlInspectionResult::class);
        $inspectionResult->shouldReceive('getInspectionResultLink')->andReturn(null);
        $inspectionResult->shouldReceive('getIndexStatusResult')->andReturn(null);
        $inspectionResult->shouldReceive('getMobileUsabilityResult')->andReturn(null);

        $inspectResponse = Mockery::mock(InspectUrlIndexResponse::class);
        $inspectResponse->shouldReceive('getInspectionResult')->andReturn($inspectionResult);

        $urlInspectionResource = Mockery::mock(SearchConsoleService\Resource\UrlInspectionIndex::class);
        $urlInspectionResource->shouldReceive('inspect')->andReturn($inspectResponse);

        $searchConsoleService = Mockery::mock(SearchConsoleService::class);
        $searchConsoleService->urlInspection_index = $urlInspectionResource;

        $reflection = new ReflectionClass($console);
        $property = $reflection->getProperty('searchConsoleService');
        $property->setValue($console, $searchConsoleService);

        $result = $console->inspectUrl('https://example.com/', 'https://example.com/page');

        expect($result)->toBeInstanceOf(UrlInspectionResult::class)
            ->and($result->inspectionResultLink)->toBe('')
            ->and($result->verdict)->toBe('VERDICT_UNSPECIFIED');
    });

    it('throws exception when url inspection fails', function (): void {
        $console = createGoogleConsole();

        $urlInspectionResource = Mockery::mock(SearchConsoleService\Resource\UrlInspectionIndex::class);
        $urlInspectionResource->shouldReceive('inspect')
            ->andThrow(new GoogleServiceException('Quota exceeded', 429));

        $searchConsoleService = Mockery::mock(SearchConsoleService::class);
        $searchConsoleService->urlInspection_index = $urlInspectionResource;

        $reflection = new ReflectionClass($console);
        $property = $reflection->getProperty('searchConsoleService');
        $property->setValue($console, $searchConsoleService);

        $console->inspectUrl('https://example.com/', 'https://example.com/page');
    })->throws(GoogleConsoleFailure::class, 'URL inspection failed');

    it('passes normalized inspection url to api when url normalizer is configured', function (): void {
        $tempFile = createTestCredentialsFile();
        $config = GoogleConfig::fromCredentialsPath($tempFile);
        $client = new GoogleClientFactory()->create($config);
        $normalizer = new UrlNormalizer(UrlNormalizationRules::forApiCalls());
        $console = new GoogleConsole($client, urlNormalizer: $normalizer);
        unlink($tempFile);

        $indexStatus = Mockery::mock(IndexStatusInspectionResult::class);
        $indexStatus->shouldReceive('getVerdict')->andReturn('PASS');
        $indexStatus->shouldReceive('getCoverageState')->andReturn('Submitted and indexed');
        $indexStatus->shouldReceive('getRobotsTxtState')->andReturn('ALLOWED');
        $indexStatus->shouldReceive('getIndexingState')->andReturn('INDEXING_ALLOWED');
        $indexStatus->shouldReceive('getLastCrawlTime')->andReturn(null);
        $indexStatus->shouldReceive('getPageFetchState')->andReturn('SUCCESSFUL');
        $indexStatus->shouldReceive('getCrawledAs')->andReturn('MOBILE');
        $indexStatus->shouldReceive('getGoogleCanonical')->andReturn('https://example.com/page');
        $indexStatus->shouldReceive('getUserCanonical')->andReturn('https://example.com/page');

        $mobileUsability = Mockery::mock(MobileUsabilityInspectionResult::class);
        $mobileUsability->shouldReceive('getVerdict')->andReturn('PASS');
        $mobileUsability->shouldReceive('getIssues')->andReturn([]);

        $inspectionResult = Mockery::mock(GoogleUrlInspectionResult::class);
        $inspectionResult->shouldReceive('getInspectionResultLink')->andReturn('');
        $inspectionResult->shouldReceive('getIndexStatusResult')->andReturn($indexStatus);
        $inspectionResult->shouldReceive('getMobileUsabilityResult')->andReturn($mobileUsability);

        $inspectResponse = Mockery::mock(InspectUrlIndexResponse::class);
        $inspectResponse->shouldReceive('getInspectionResult')->andReturn($inspectionResult);

        $urlInspectionResource = Mockery::mock(SearchConsoleService\Resource\UrlInspectionIndex::class);
        $urlInspectionResource->shouldReceive('inspect')
            ->with(Mockery::on(static fn (InspectUrlIndexRequest $request): bool => $request->getInspectionUrl() === 'https://example.com/page'))
            ->andReturn($inspectResponse);

        $searchConsoleService = Mockery::mock(SearchConsoleService::class);
        $searchConsoleService->urlInspection_index = $urlInspectionResource;

        $reflection = new ReflectionClass($console);
        $property = $reflection->getProperty('searchConsoleService');
        $property->setValue($console, $searchConsoleService);

        $result = $console->inspectUrl('https://example.com/', 'https://example.com/page#section?utm_source=google&gclid=abc');

        expect($result)->toBeInstanceOf(UrlInspectionResult::class)
            ->and($result->verdict)->toBe('PASS');
    });

    it('inspectUrl uses context site url when context is provided', function (): void {
        $console = createGoogleConsole();

        $indexStatus = Mockery::mock(IndexStatusInspectionResult::class);
        $indexStatus->shouldReceive('getVerdict')->andReturn('PASS');
        $indexStatus->shouldReceive('getCoverageState')->andReturn('Submitted and indexed');
        $indexStatus->shouldReceive('getRobotsTxtState')->andReturn('ALLOWED');
        $indexStatus->shouldReceive('getIndexingState')->andReturn('INDEXING_ALLOWED');
        $indexStatus->shouldReceive('getLastCrawlTime')->andReturn(null);
        $indexStatus->shouldReceive('getPageFetchState')->andReturn('SUCCESSFUL');
        $indexStatus->shouldReceive('getCrawledAs')->andReturn('MOBILE');
        $indexStatus->shouldReceive('getGoogleCanonical')->andReturn('https://example.com/page');
        $indexStatus->shouldReceive('getUserCanonical')->andReturn('https://example.com/page');

        $mobileUsability = Mockery::mock(MobileUsabilityInspectionResult::class);
        $mobileUsability->shouldReceive('getVerdict')->andReturn('PASS');
        $mobileUsability->shouldReceive('getIssues')->andReturn([]);

        $inspectionResult = Mockery::mock(GoogleUrlInspectionResult::class);
        $inspectionResult->shouldReceive('getInspectionResultLink')->andReturn('');
        $inspectionResult->shouldReceive('getIndexStatusResult')->andReturn($indexStatus);
        $inspectionResult->shouldReceive('getMobileUsabilityResult')->andReturn($mobileUsability);

        $inspectResponse = Mockery::mock(InspectUrlIndexResponse::class);
        $inspectResponse->shouldReceive('getInspectionResult')->andReturn($inspectionResult);

        $urlInspectionResource = Mockery::mock(SearchConsoleService\Resource\UrlInspectionIndex::class);
        $urlInspectionResource->shouldReceive('inspect')
            ->with(Mockery::on(static fn (InspectUrlIndexRequest $request): bool => $request->getSiteUrl() === 'sc-domain:example.com'
                && $request->getInspectionUrl() === 'https://example.com/page'))
            ->andReturn($inspectResponse);

        $searchConsoleService = Mockery::mock(SearchConsoleService::class);
        $searchConsoleService->urlInspection_index = $urlInspectionResource;

        $reflection = new ReflectionClass($console);
        $property = $reflection->getProperty('searchConsoleService');
        $property->setValue($console, $searchConsoleService);

        $context = new InspectionContext(siteUrl: 'sc-domain:example.com');
        $result = $console->inspectUrl('https://example.com/', 'https://example.com/page', null, $context);

        expect($result)->toBeInstanceOf(UrlInspectionResult::class)
            ->and($result->verdict)->toBe('PASS');
    });

    it('inspectUrl uses context url normalizer when context is provided', function (): void {
        $console = createGoogleConsole();

        $indexStatus = Mockery::mock(IndexStatusInspectionResult::class);
        $indexStatus->shouldReceive('getVerdict')->andReturn('PASS');
        $indexStatus->shouldReceive('getCoverageState')->andReturn('Submitted and indexed');
        $indexStatus->shouldReceive('getRobotsTxtState')->andReturn('ALLOWED');
        $indexStatus->shouldReceive('getIndexingState')->andReturn('INDEXING_ALLOWED');
        $indexStatus->shouldReceive('getLastCrawlTime')->andReturn(null);
        $indexStatus->shouldReceive('getPageFetchState')->andReturn('SUCCESSFUL');
        $indexStatus->shouldReceive('getCrawledAs')->andReturn('MOBILE');
        $indexStatus->shouldReceive('getGoogleCanonical')->andReturn('https://example.com/page');
        $indexStatus->shouldReceive('getUserCanonical')->andReturn('https://example.com/page');

        $mobileUsability = Mockery::mock(MobileUsabilityInspectionResult::class);
        $mobileUsability->shouldReceive('getVerdict')->andReturn('PASS');
        $mobileUsability->shouldReceive('getIssues')->andReturn([]);

        $inspectionResult = Mockery::mock(GoogleUrlInspectionResult::class);
        $inspectionResult->shouldReceive('getInspectionResultLink')->andReturn('');
        $inspectionResult->shouldReceive('getIndexStatusResult')->andReturn($indexStatus);
        $inspectionResult->shouldReceive('getMobileUsabilityResult')->andReturn($mobileUsability);

        $inspectResponse = Mockery::mock(InspectUrlIndexResponse::class);
        $inspectResponse->shouldReceive('getInspectionResult')->andReturn($inspectionResult);

        $urlInspectionResource = Mockery::mock(SearchConsoleService\Resource\UrlInspectionIndex::class);
        $urlInspectionResource->shouldReceive('inspect')
            ->with(Mockery::on(static fn (InspectUrlIndexRequest $request): bool => $request->getInspectionUrl() === 'https://example.com/page'))
            ->andReturn($inspectResponse);

        $searchConsoleService = Mockery::mock(SearchConsoleService::class);
        $searchConsoleService->urlInspection_index = $urlInspectionResource;

        $reflection = new ReflectionClass($console);
        $property = $reflection->getProperty('searchConsoleService');
        $property->setValue($console, $searchConsoleService);

        $contextNormalizer = new UrlNormalizer(UrlNormalizationRules::forApiCalls());
        $context = new InspectionContext(urlNormalizer: $contextNormalizer);
        $result = $console->inspectUrl('https://example.com/', 'https://example.com/page#anchor?utm_source=test', null, $context);

        expect($result)->toBeInstanceOf(UrlInspectionResult::class)
            ->and($result->verdict)->toBe('PASS');
    });

    it('checkIndexStatus returns IndexStatusCheckResult with status and reason codes from inspectUrl', function (): void {
        $console = createGoogleConsole();

        $indexStatus = Mockery::mock(IndexStatusInspectionResult::class);
        $indexStatus->shouldReceive('getVerdict')->andReturn('PASS');
        $indexStatus->shouldReceive('getCoverageState')->andReturn('Submitted and indexed');
        $indexStatus->shouldReceive('getRobotsTxtState')->andReturn('ALLOWED');
        $indexStatus->shouldReceive('getIndexingState')->andReturn('INDEXING_ALLOWED');
        $indexStatus->shouldReceive('getLastCrawlTime')->andReturn('2024-01-15T10:30:00Z');
        $indexStatus->shouldReceive('getPageFetchState')->andReturn('SUCCESSFUL');
        $indexStatus->shouldReceive('getCrawledAs')->andReturn('MOBILE');
        $indexStatus->shouldReceive('getGoogleCanonical')->andReturn('https://example.com/page');
        $indexStatus->shouldReceive('getUserCanonical')->andReturn('https://example.com/page');

        $mobileUsability = Mockery::mock(MobileUsabilityInspectionResult::class);
        $mobileUsability->shouldReceive('getVerdict')->andReturn('PASS');
        $mobileUsability->shouldReceive('getIssues')->andReturn([]);

        $inspectionResult = Mockery::mock(GoogleUrlInspectionResult::class);
        $inspectionResult->shouldReceive('getInspectionResultLink')->andReturn('https://search.google.com/search-console/inspect');
        $inspectionResult->shouldReceive('getIndexStatusResult')->andReturn($indexStatus);
        $inspectionResult->shouldReceive('getMobileUsabilityResult')->andReturn($mobileUsability);

        $inspectResponse = Mockery::mock(InspectUrlIndexResponse::class);
        $inspectResponse->shouldReceive('getInspectionResult')->andReturn($inspectionResult);

        $urlInspectionResource = Mockery::mock(SearchConsoleService\Resource\UrlInspectionIndex::class);
        $urlInspectionResource->shouldReceive('inspect')->andReturn($inspectResponse);

        $searchConsoleService = Mockery::mock(SearchConsoleService::class);
        $searchConsoleService->urlInspection_index = $urlInspectionResource;

        $reflection = new ReflectionClass($console);
        $property = $reflection->getProperty('searchConsoleService');
        $property->setValue($console, $searchConsoleService);

        $result = $console->checkIndexStatus('https://example.com/', 'https://example.com/page');

        expect($result)->toBeInstanceOf(IndexStatusCheckResult::class)
            ->and($result->url)->toBe('https://example.com/page')
            ->and($result->status)->toBe(IndexingCheckStatus::INDEXED)
            ->and($result->reasonCodes)->not->toBeEmpty();
    });

    it('checkIndexStatus returns IndexStatusCheckResult when API returns no index status data', function (): void {
        $console = createGoogleConsole();

        $inspectionResult = Mockery::mock(GoogleUrlInspectionResult::class);
        $inspectionResult->shouldReceive('getInspectionResultLink')->andReturn('');
        $inspectionResult->shouldReceive('getIndexStatusResult')->andReturn(null);
        $inspectionResult->shouldReceive('getMobileUsabilityResult')->andReturn(null);

        $inspectResponse = Mockery::mock(InspectUrlIndexResponse::class);
        $inspectResponse->shouldReceive('getInspectionResult')->andReturn($inspectionResult);

        $urlInspectionResource = Mockery::mock(SearchConsoleService\Resource\UrlInspectionIndex::class);
        $urlInspectionResource->shouldReceive('inspect')->andReturn($inspectResponse);

        $searchConsoleService = Mockery::mock(SearchConsoleService::class);
        $searchConsoleService->urlInspection_index = $urlInspectionResource;

        $reflection = new ReflectionClass($console);
        $property = $reflection->getProperty('searchConsoleService');
        $property->setValue($console, $searchConsoleService);

        $result = $console->checkIndexStatus('https://example.com/', 'https://example.com/page');

        expect($result)->toBeInstanceOf(IndexStatusCheckResult::class)
            ->and($result->url)->toBe('https://example.com/page')
            ->and($result->status)->toBe(IndexingCheckStatus::UNKNOWN)
            ->and($result->reasonCodes)->toContain(IndexingCheckReasonCode::INSUFFICIENT_DATA)
            ->and($result->sourceType)->toBe(IndexingCheckSourceType::AUTHORITATIVE);
    });

    it('checkIndexStatus uses normalized url in result when normalizer is configured', function (): void {
        $tempFile = createTestCredentialsFile();
        $config = GoogleConfig::fromCredentialsPath($tempFile);
        $client = new GoogleClientFactory()->create($config);
        $normalizer = new UrlNormalizer(UrlNormalizationRules::forApiCalls());
        $console = new GoogleConsole($client, urlNormalizer: $normalizer);
        unlink($tempFile);

        $indexStatus = Mockery::mock(IndexStatusInspectionResult::class);
        $indexStatus->shouldReceive('getVerdict')->andReturn('PASS');
        $indexStatus->shouldReceive('getCoverageState')->andReturn('Submitted and indexed');
        $indexStatus->shouldReceive('getRobotsTxtState')->andReturn('ALLOWED');
        $indexStatus->shouldReceive('getIndexingState')->andReturn('INDEXING_ALLOWED');
        $indexStatus->shouldReceive('getLastCrawlTime')->andReturn(null);
        $indexStatus->shouldReceive('getPageFetchState')->andReturn('SUCCESSFUL');
        $indexStatus->shouldReceive('getCrawledAs')->andReturn('MOBILE');
        $indexStatus->shouldReceive('getGoogleCanonical')->andReturn('https://example.com/page');
        $indexStatus->shouldReceive('getUserCanonical')->andReturn('https://example.com/page');

        $mobileUsability = Mockery::mock(MobileUsabilityInspectionResult::class);
        $mobileUsability->shouldReceive('getVerdict')->andReturn('PASS');
        $mobileUsability->shouldReceive('getIssues')->andReturn([]);

        $inspectionResult = Mockery::mock(GoogleUrlInspectionResult::class);
        $inspectionResult->shouldReceive('getInspectionResultLink')->andReturn('');
        $inspectionResult->shouldReceive('getIndexStatusResult')->andReturn($indexStatus);
        $inspectionResult->shouldReceive('getMobileUsabilityResult')->andReturn($mobileUsability);

        $inspectResponse = Mockery::mock(InspectUrlIndexResponse::class);
        $inspectResponse->shouldReceive('getInspectionResult')->andReturn($inspectionResult);

        $urlInspectionResource = Mockery::mock(SearchConsoleService\Resource\UrlInspectionIndex::class);
        $urlInspectionResource->shouldReceive('inspect')
            ->with(Mockery::on(static fn (InspectUrlIndexRequest $request): bool => $request->getInspectionUrl() === 'https://example.com/page'))
            ->andReturn($inspectResponse);

        $searchConsoleService = Mockery::mock(SearchConsoleService::class);
        $searchConsoleService->urlInspection_index = $urlInspectionResource;

        $reflection = new ReflectionClass($console);
        $property = $reflection->getProperty('searchConsoleService');
        $property->setValue($console, $searchConsoleService);

        $result = $console->checkIndexStatus('https://example.com/', 'https://example.com/page#section?utm_source=google&gclid=abc');

        expect($result)->toBeInstanceOf(IndexStatusCheckResult::class)
            ->and($result->url)->toBe('https://example.com/page');
    });

    it('inspectBatchUrls uses context site url when context is provided', function (): void {
        $console = createGoogleConsole();

        $indexStatus = Mockery::mock(IndexStatusInspectionResult::class);
        $indexStatus->shouldReceive('getVerdict')->andReturn('PASS');
        $indexStatus->shouldReceive('getCoverageState')->andReturn('Submitted and indexed');
        $indexStatus->shouldReceive('getRobotsTxtState')->andReturn('ALLOWED');
        $indexStatus->shouldReceive('getIndexingState')->andReturn('INDEXING_ALLOWED');
        $indexStatus->shouldReceive('getLastCrawlTime')->andReturn(null);
        $indexStatus->shouldReceive('getPageFetchState')->andReturn('SUCCESSFUL');
        $indexStatus->shouldReceive('getCrawledAs')->andReturn('MOBILE');
        $indexStatus->shouldReceive('getGoogleCanonical')->andReturn('https://example.com/');
        $indexStatus->shouldReceive('getUserCanonical')->andReturn('https://example.com/');

        $mobileUsability = Mockery::mock(MobileUsabilityInspectionResult::class);
        $mobileUsability->shouldReceive('getVerdict')->andReturn('PASS');
        $mobileUsability->shouldReceive('getIssues')->andReturn([]);

        $inspectionResult = Mockery::mock(GoogleUrlInspectionResult::class);
        $inspectionResult->shouldReceive('getInspectionResultLink')->andReturn('');
        $inspectionResult->shouldReceive('getIndexStatusResult')->andReturn($indexStatus);
        $inspectionResult->shouldReceive('getMobileUsabilityResult')->andReturn($mobileUsability);

        $inspectResponse = Mockery::mock(InspectUrlIndexResponse::class);
        $inspectResponse->shouldReceive('getInspectionResult')->andReturn($inspectionResult);

        $urlInspectionResource = Mockery::mock(SearchConsoleService\Resource\UrlInspectionIndex::class);
        $urlInspectionResource->shouldReceive('inspect')
            ->with(Mockery::on(static fn (InspectUrlIndexRequest $request): bool => $request->getSiteUrl() === 'sc-domain:example.com'))
            ->andReturn($inspectResponse);

        $searchConsoleService = Mockery::mock(SearchConsoleService::class);
        $searchConsoleService->urlInspection_index = $urlInspectionResource;

        $reflection = new ReflectionClass($console);
        $property = $reflection->getProperty('searchConsoleService');
        $property->setValue($console, $searchConsoleService);

        $context = new InspectionContext(siteUrl: 'sc-domain:example.com');
        $result = $console->inspectBatchUrls('https://example.com/', ['https://example.com/'], [], null, $context);

        expect($result)->toBeInstanceOf(BatchUrlInspectionResult::class)
            ->and($result->batchVerdict)->toBe(BatchVerdict::PASS)
            ->and($result->perUrlResults)->toHaveCount(1);
    });

    it('initializes webmasters service lazily', function (): void {
        $console = createGoogleConsole();

        $reflection = new ReflectionClass($console);
        $method = $reflection->getMethod('getWebmastersService');

        $service = $method->invoke($console);

        expect($service)->toBeInstanceOf(WebmastersService::class);

        $serviceAgain = $method->invoke($console);

        expect($serviceAgain)->toBe($service);
    });

    it('initializes search pekral-google service lazily', function (): void {
        $console = createGoogleConsole();

        $reflection = new ReflectionClass($console);
        $method = $reflection->getMethod('getSearchConsoleService');

        $service = $method->invoke($console);

        expect($service)->toBeInstanceOf(SearchConsoleService::class);

        $serviceAgain = $method->invoke($console);

        expect($serviceAgain)->toBe($service);
    });

    it('inspects url with mobile usability issues', function (): void {
        $console = createGoogleConsole();

        $indexStatus = Mockery::mock(IndexStatusInspectionResult::class);
        $indexStatus->shouldReceive('getVerdict')->andReturn('PASS');
        $indexStatus->shouldReceive('getCoverageState')->andReturn('Submitted and indexed');
        $indexStatus->shouldReceive('getRobotsTxtState')->andReturn('ALLOWED');
        $indexStatus->shouldReceive('getIndexingState')->andReturn('INDEXING_ALLOWED');
        $indexStatus->shouldReceive('getLastCrawlTime')->andReturn(null);
        $indexStatus->shouldReceive('getPageFetchState')->andReturn('SUCCESSFUL');
        $indexStatus->shouldReceive('getCrawledAs')->andReturn('MOBILE');
        $indexStatus->shouldReceive('getGoogleCanonical')->andReturn('https://example.com/page');
        $indexStatus->shouldReceive('getUserCanonical')->andReturn('https://example.com/page');

        $mobileIssue = new class () {

            public function getIssueType(): string
            {
                return 'TEXT_TOO_SMALL';
            }

        };

        $mobileUsability = Mockery::mock(MobileUsabilityInspectionResult::class);
        $mobileUsability->shouldReceive('getVerdict')->andReturn('FAIL');
        $mobileUsability->shouldReceive('getIssues')->andReturn([$mobileIssue]);

        $inspectionResult = Mockery::mock(GoogleUrlInspectionResult::class);
        $inspectionResult->shouldReceive('getInspectionResultLink')->andReturn('https://search.google.com/search-console/inspect');
        $inspectionResult->shouldReceive('getIndexStatusResult')->andReturn($indexStatus);
        $inspectionResult->shouldReceive('getMobileUsabilityResult')->andReturn($mobileUsability);

        $inspectResponse = Mockery::mock(InspectUrlIndexResponse::class);
        $inspectResponse->shouldReceive('getInspectionResult')->andReturn($inspectionResult);

        $urlInspectionResource = Mockery::mock(SearchConsoleService\Resource\UrlInspectionIndex::class);
        $urlInspectionResource->shouldReceive('inspect')->andReturn($inspectResponse);

        $searchConsoleService = Mockery::mock(SearchConsoleService::class);
        $searchConsoleService->urlInspection_index = $urlInspectionResource;

        $reflection = new ReflectionClass($console);
        $property = $reflection->getProperty('searchConsoleService');
        $property->setValue($console, $searchConsoleService);

        $result = $console->inspectUrl('https://example.com/', 'https://example.com/page');

        expect($result)->toBeInstanceOf(UrlInspectionResult::class)
            ->and($result->isMobileFriendly)->toBeFalse()
            ->and($result->mobileUsabilityIssue)->toBe('TEXT_TOO_SMALL');
    });

    it('throws exception when search analytics api fails', function (): void {
        $console = createGoogleConsole();

        $searchAnalyticsResource = Mockery::mock(WebmastersService\Resource\Searchanalytics::class);
        $searchAnalyticsResource->shouldReceive('query')
            ->andThrow(new GoogleServiceException('Permission denied', 403));

        $webmastersService = Mockery::mock(WebmastersService::class);
        $webmastersService->searchanalytics = $searchAnalyticsResource;

        $reflection = new ReflectionClass($console);
        $property = $reflection->getProperty('webmastersService');
        $property->setValue($console, $webmastersService);

        $console->getSearchAnalytics(
            'https://example.com/',
            new DateTimeImmutable('2024-01-01'),
            new DateTimeImmutable('2024-01-31'),
        );
    })->throws(GoogleConsoleFailure::class, 'Search analytics failed for site \'https://example.com/\'');

    it('formats api error with error details', function (): void {
        $console = createGoogleConsole();

        $exception = new GoogleServiceException('API Error', 403, null, [
            ['message' => 'User does not have permission', 'reason' => 'forbidden'],
        ]);

        $searchAnalyticsResource = Mockery::mock(WebmastersService\Resource\Searchanalytics::class);
        $searchAnalyticsResource->shouldReceive('query')
            ->andThrow($exception);

        $webmastersService = Mockery::mock(WebmastersService::class);
        $webmastersService->searchanalytics = $searchAnalyticsResource;

        $reflection = new ReflectionClass($console);
        $property = $reflection->getProperty('webmastersService');
        $property->setValue($console, $webmastersService);

        expect(fn (): array => $console->getSearchAnalytics(
            'https://example.com/',
            new DateTimeImmutable('2024-01-01'),
            new DateTimeImmutable('2024-01-31'),
        ))->toThrow(
            GoogleConsoleFailure::class,
            'User does not have permission (reason: forbidden)',
        );
    });

    it('formats api error without error details', function (): void {
        $console = createGoogleConsole();

        $exception = new GoogleServiceException('Simple error message', 500);

        $searchAnalyticsResource = Mockery::mock(WebmastersService\Resource\Searchanalytics::class);
        $searchAnalyticsResource->shouldReceive('query')
            ->andThrow($exception);

        $webmastersService = Mockery::mock(WebmastersService::class);
        $webmastersService->searchanalytics = $searchAnalyticsResource;

        $reflection = new ReflectionClass($console);
        $property = $reflection->getProperty('webmastersService');
        $property->setValue($console, $webmastersService);

        expect(fn (): array => $console->getSearchAnalytics(
            'https://example.com/',
            new DateTimeImmutable('2024-01-01'),
            new DateTimeImmutable('2024-01-31'),
        ))->toThrow(
            GoogleConsoleFailure::class,
            'Simple error message',
        );
    });

    it('requests indexing successfully with notify time', function (): void {
        $responseBody = json_encode([
            'urlNotificationMetadata' => [
                'url' => 'https://example.com/page',
                'latestUpdate' => [
                    'notifyTime' => '2024-01-15T10:30:00Z',
                ],
            ],
        ]);

        $httpClient = Mockery::mock(HttpClient::class);
        $httpClient->shouldReceive('send')
            ->andReturn(new Response(200, [], $responseBody));

        $client = Mockery::mock(Client::class);
        $client->shouldReceive('authorize')->andReturn($httpClient);

        $console = new GoogleConsole($client);

        $result = $console->requestIndexing('https://example.com/page', IndexingNotificationType::URL_UPDATED);

        expect($result)->toBeInstanceOf(IndexingResult::class)
            ->and($result->url)->toBe('https://example.com/page')
            ->and($result->type)->toBe(IndexingNotificationType::URL_UPDATED)
            ->and($result->notifyTime)->not->toBeNull()
            ->and($result->notifyTime?->format('Y-m-d'))->toBe('2024-01-15');
    });

    it('requestIndexing normalizes url when url normalizer is configured', function (): void {
        $responseBody = json_encode([
            'urlNotificationMetadata' => [
                'url' => 'https://example.com/page',
                'latestUpdate' => ['notifyTime' => '2024-01-15T10:30:00Z'],
            ],
        ]);

        $httpClient = Mockery::mock(HttpClient::class);
        $httpClient->shouldReceive('send')
            ->with(Mockery::on(static function (Request $request): bool {
                $body = (string) $request->getBody();
                $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

                return ($data['url'] ?? '') === 'https://example.com/page';
            }))
            ->andReturn(new Response(200, [], $responseBody));

        $client = Mockery::mock(Client::class);
        $client->shouldReceive('authorize')->andReturn($httpClient);

        $normalizer = new UrlNormalizer(UrlNormalizationRules::forApiCalls());
        $console = new GoogleConsole($client, urlNormalizer: $normalizer);

        $result = $console->requestIndexing('https://example.com/page#anchor?utm_source=test', IndexingNotificationType::URL_UPDATED);

        expect($result)->toBeInstanceOf(IndexingResult::class)
            ->and($result->url)->toBe('https://example.com/page');
    });

    it('requests indexing successfully without notify time', function (): void {
        $responseBody = json_encode([
            'urlNotificationMetadata' => [
                'url' => 'https://example.com/page',
            ],
        ]);

        $httpClient = Mockery::mock(HttpClient::class);
        $httpClient->shouldReceive('send')
            ->andReturn(new Response(200, [], $responseBody));

        $client = Mockery::mock(Client::class);
        $client->shouldReceive('authorize')->andReturn($httpClient);

        $console = new GoogleConsole($client);

        $result = $console->requestIndexing('https://example.com/page');

        expect($result)->toBeInstanceOf(IndexingResult::class)
            ->and($result->url)->toBe('https://example.com/page')
            ->and($result->notifyTime)->toBeNull();
    });

    it('requests url deletion', function (): void {
        $responseBody = json_encode([
            'urlNotificationMetadata' => [
                'url' => 'https://example.com/page',
                'latestRemove' => [
                    'notifyTime' => '2024-01-15T10:30:00Z',
                ],
            ],
        ]);

        $httpClient = Mockery::mock(HttpClient::class);
        $httpClient->shouldReceive('send')
            ->andReturn(new Response(200, [], $responseBody));

        $client = Mockery::mock(Client::class);
        $client->shouldReceive('authorize')->andReturn($httpClient);

        $console = new GoogleConsole($client);

        $result = $console->requestIndexing('https://example.com/page', IndexingNotificationType::URL_DELETED);

        expect($result)->toBeInstanceOf(IndexingResult::class)
            ->and($result->type)->toBe(IndexingNotificationType::URL_DELETED);
    });

    it('uses fallback url when not in response', function (): void {
        $responseBody = json_encode([
            'urlNotificationMetadata' => [],
        ]);

        $httpClient = Mockery::mock(HttpClient::class);
        $httpClient->shouldReceive('send')
            ->andReturn(new Response(200, [], $responseBody));

        $client = Mockery::mock(Client::class);
        $client->shouldReceive('authorize')->andReturn($httpClient);

        $console = new GoogleConsole($client);

        $result = $console->requestIndexing('https://example.com/fallback');

        expect($result->url)->toBe('https://example.com/fallback');
    });

    it('throws exception when indexing request fails', function (): void {
        $httpClient = Mockery::mock(HttpClient::class);
        $httpClient->shouldReceive('send')
            ->andThrow(new RuntimeException('Network error'));

        $client = Mockery::mock(Client::class);
        $client->shouldReceive('authorize')->andReturn($httpClient);

        $console = new GoogleConsole($client);

        $console->requestIndexing('https://example.com/page');
    })->throws(GoogleConsoleFailure::class, 'Indexing request failed for URL \'https://example.com/page\'');

    it('inspectBatchUrls returns per-url results and aggregation', function (): void {
        $console = createGoogleConsole();

        $indexStatusIndexed = Mockery::mock(IndexStatusInspectionResult::class);
        $indexStatusIndexed->shouldReceive('getVerdict')->andReturn('PASS');
        $indexStatusIndexed->shouldReceive('getCoverageState')->andReturn('Submitted and indexed');
        $indexStatusIndexed->shouldReceive('getRobotsTxtState')->andReturn('ALLOWED');
        $indexStatusIndexed->shouldReceive('getIndexingState')->andReturn('INDEXING_ALLOWED');
        $indexStatusIndexed->shouldReceive('getLastCrawlTime')->andReturn(null);
        $indexStatusIndexed->shouldReceive('getPageFetchState')->andReturn('SUCCESSFUL');
        $indexStatusIndexed->shouldReceive('getCrawledAs')->andReturn('MOBILE');
        $indexStatusIndexed->shouldReceive('getGoogleCanonical')->andReturn('https://example.com/page-a');
        $indexStatusIndexed->shouldReceive('getUserCanonical')->andReturn('https://example.com/page-a');

        $indexStatusNotIndexed = Mockery::mock(IndexStatusInspectionResult::class);
        $indexStatusNotIndexed->shouldReceive('getVerdict')->andReturn('FAIL');
        $indexStatusNotIndexed->shouldReceive('getCoverageState')->andReturn('Not indexed');
        $indexStatusNotIndexed->shouldReceive('getRobotsTxtState')->andReturn('ALLOWED');
        $indexStatusNotIndexed->shouldReceive('getIndexingState')->andReturn('INDEXING_ALLOWED');
        $indexStatusNotIndexed->shouldReceive('getLastCrawlTime')->andReturn(null);
        $indexStatusNotIndexed->shouldReceive('getPageFetchState')->andReturn('SUCCESSFUL');
        $indexStatusNotIndexed->shouldReceive('getCrawledAs')->andReturn('MOBILE');
        $indexStatusNotIndexed->shouldReceive('getGoogleCanonical')->andReturn('https://example.com/page-b');
        $indexStatusNotIndexed->shouldReceive('getUserCanonical')->andReturn('https://example.com/page-b');

        $mobileUsability = Mockery::mock(MobileUsabilityInspectionResult::class);
        $mobileUsability->shouldReceive('getVerdict')->andReturn('PASS');
        $mobileUsability->shouldReceive('getIssues')->andReturn([]);

        $inspectionResultA = Mockery::mock(GoogleUrlInspectionResult::class);
        $inspectionResultA->shouldReceive('getInspectionResultLink')->andReturn('');
        $inspectionResultA->shouldReceive('getIndexStatusResult')->andReturn($indexStatusIndexed);
        $inspectionResultA->shouldReceive('getMobileUsabilityResult')->andReturn($mobileUsability);

        $inspectionResultB = Mockery::mock(GoogleUrlInspectionResult::class);
        $inspectionResultB->shouldReceive('getInspectionResultLink')->andReturn('');
        $inspectionResultB->shouldReceive('getIndexStatusResult')->andReturn($indexStatusNotIndexed);
        $inspectionResultB->shouldReceive('getMobileUsabilityResult')->andReturn($mobileUsability);

        $inspectResponseA = Mockery::mock(InspectUrlIndexResponse::class);
        $inspectResponseA->shouldReceive('getInspectionResult')->andReturn($inspectionResultA);
        $inspectResponseB = Mockery::mock(InspectUrlIndexResponse::class);
        $inspectResponseB->shouldReceive('getInspectionResult')->andReturn($inspectionResultB);

        $urlInspectionResource = Mockery::mock(SearchConsoleService\Resource\UrlInspectionIndex::class);
        $urlInspectionResource->shouldReceive('inspect')
            ->andReturnUsing(static fn (InspectUrlIndexRequest $request) => $request->getInspectionUrl() === 'https://example.com/page-a'
                ? $inspectResponseA
                : $inspectResponseB);

        $searchConsoleService = Mockery::mock(SearchConsoleService::class);
        $searchConsoleService->urlInspection_index = $urlInspectionResource;

        $reflection = new ReflectionClass($console);
        $property = $reflection->getProperty('searchConsoleService');
        $property->setValue($console, $searchConsoleService);

        $result = $console->inspectBatchUrls(
            'https://example.com/',
            ['https://example.com/page-a', 'https://example.com/page-b'],
        );

        expect($result)->toBeInstanceOf(BatchUrlInspectionResult::class)
            ->and($result->batchVerdict)->toBe(BatchVerdict::PASS)
            ->and($result->perUrlResults)->toHaveCount(2)
            ->and($result->aggregation->indexedCount)->toBe(1)
            ->and($result->aggregation->notIndexedCount)->toBe(1)
            ->and($result->aggregation->unknownCount)->toBe(0)
            ->and($result->perUrlResults['https://example.com/page-a']->status)->toBe(IndexingCheckStatus::INDEXED)
            ->and($result->perUrlResults['https://example.com/page-b']->status)->toBe(IndexingCheckStatus::NOT_INDEXED);
    });

    it('inspectBatchUrls returns FAIL when critical url is NOT_INDEXED', function (): void {
        $console = createGoogleConsole();

        $indexStatusNotIndexed = Mockery::mock(IndexStatusInspectionResult::class);
        $indexStatusNotIndexed->shouldReceive('getVerdict')->andReturn('FAIL');
        $indexStatusNotIndexed->shouldReceive('getCoverageState')->andReturn('Not indexed');
        $indexStatusNotIndexed->shouldReceive('getRobotsTxtState')->andReturn('ALLOWED');
        $indexStatusNotIndexed->shouldReceive('getIndexingState')->andReturn('INDEXING_ALLOWED');
        $indexStatusNotIndexed->shouldReceive('getLastCrawlTime')->andReturn(null);
        $indexStatusNotIndexed->shouldReceive('getPageFetchState')->andReturn('SUCCESSFUL');
        $indexStatusNotIndexed->shouldReceive('getCrawledAs')->andReturn('MOBILE');
        $indexStatusNotIndexed->shouldReceive('getGoogleCanonical')->andReturn('https://example.com/critical');
        $indexStatusNotIndexed->shouldReceive('getUserCanonical')->andReturn('https://example.com/critical');

        $mobileUsability = Mockery::mock(MobileUsabilityInspectionResult::class);
        $mobileUsability->shouldReceive('getVerdict')->andReturn('PASS');
        $mobileUsability->shouldReceive('getIssues')->andReturn([]);

        $inspectionResult = Mockery::mock(GoogleUrlInspectionResult::class);
        $inspectionResult->shouldReceive('getInspectionResultLink')->andReturn('');
        $inspectionResult->shouldReceive('getIndexStatusResult')->andReturn($indexStatusNotIndexed);
        $inspectionResult->shouldReceive('getMobileUsabilityResult')->andReturn($mobileUsability);

        $inspectResponse = Mockery::mock(InspectUrlIndexResponse::class);
        $inspectResponse->shouldReceive('getInspectionResult')->andReturn($inspectionResult);

        $urlInspectionResource = Mockery::mock(SearchConsoleService\Resource\UrlInspectionIndex::class);
        $urlInspectionResource->shouldReceive('inspect')->andReturn($inspectResponse);

        $searchConsoleService = Mockery::mock(SearchConsoleService::class);
        $searchConsoleService->urlInspection_index = $urlInspectionResource;

        $reflection = new ReflectionClass($console);
        $property = $reflection->getProperty('searchConsoleService');
        $property->setValue($console, $searchConsoleService);

        $result = $console->inspectBatchUrls(
            'https://example.com/',
            ['https://example.com/critical'],
            ['https://example.com/critical'],
        );

        expect($result->batchVerdict)->toBe(BatchVerdict::FAIL)
            ->and($result->criticalUrlResults)->toHaveCount(1)
            ->and($result->criticalUrlResults[0]->status)->toBe(IndexingCheckStatus::NOT_INDEXED);
    });

    it('inspectBatchUrls returns PASS when no critical urls', function (): void {
        $console = createGoogleConsole();

        $result = $console->inspectBatchUrls('https://example.com/', [], []);

        expect($result->batchVerdict)->toBe(BatchVerdict::PASS)
            ->and($result->perUrlResults)->toBeEmpty()
            ->and($result->criticalUrlResults)->toBeEmpty()
            ->and($result->aggregation->indexedCount)->toBe(0)
            ->and($result->aggregation->notIndexedCount)->toBe(0)
            ->and($result->aggregation->unknownCount)->toBe(0);
    });

    it('inspectBatchUrls throws BatchSizeLimitExceeded when batch exceeds configured maximum', function (): void {
        $console = createGoogleConsole(createBatchConfig(maxBatchSize: 2));

        $console->inspectBatchUrls(
            'https://example.com/',
            ['https://example.com/a', 'https://example.com/b', 'https://example.com/c'],
        );
    })->throws(BatchSizeLimitExceeded::class, 'Batch size 3 exceeds maximum of 2 URLs');

    it('inspectBatchUrls allows batch at exact limit', function (): void {
        $console = createGoogleConsole(createBatchConfig(maxBatchSize: 2));

        $indexStatus = Mockery::mock(IndexStatusInspectionResult::class);
        $indexStatus->shouldReceive('getVerdict')->andReturn('PASS');
        $indexStatus->shouldReceive('getCoverageState')->andReturn('Submitted and indexed');
        $indexStatus->shouldReceive('getRobotsTxtState')->andReturn('ALLOWED');
        $indexStatus->shouldReceive('getIndexingState')->andReturn('INDEXING_ALLOWED');
        $indexStatus->shouldReceive('getLastCrawlTime')->andReturn(null);
        $indexStatus->shouldReceive('getPageFetchState')->andReturn('SUCCESSFUL');
        $indexStatus->shouldReceive('getCrawledAs')->andReturn('MOBILE');
        $indexStatus->shouldReceive('getGoogleCanonical')->andReturn('https://example.com/a');
        $indexStatus->shouldReceive('getUserCanonical')->andReturn('https://example.com/a');

        $mobileUsability = Mockery::mock(MobileUsabilityInspectionResult::class);
        $mobileUsability->shouldReceive('getVerdict')->andReturn('PASS');
        $mobileUsability->shouldReceive('getIssues')->andReturn([]);

        $inspectionResult = Mockery::mock(GoogleUrlInspectionResult::class);
        $inspectionResult->shouldReceive('getInspectionResultLink')->andReturn('');
        $inspectionResult->shouldReceive('getIndexStatusResult')->andReturn($indexStatus);
        $inspectionResult->shouldReceive('getMobileUsabilityResult')->andReturn($mobileUsability);

        $inspectResponse = Mockery::mock(InspectUrlIndexResponse::class);
        $inspectResponse->shouldReceive('getInspectionResult')->andReturn($inspectionResult);

        $urlInspectionResource = Mockery::mock(SearchConsoleService\Resource\UrlInspectionIndex::class);
        $urlInspectionResource->shouldReceive('inspect')->andReturn($inspectResponse);

        $searchConsoleService = Mockery::mock(SearchConsoleService::class);
        $searchConsoleService->urlInspection_index = $urlInspectionResource;

        $reflection = new ReflectionClass($console);
        $property = $reflection->getProperty('searchConsoleService');
        $property->setValue($console, $searchConsoleService);

        $result = $console->inspectBatchUrls(
            'https://example.com/',
            ['https://example.com/a', 'https://example.com/b'],
        );

        expect($result->perUrlResults)->toHaveCount(2);
    });

    it('inspectBatchUrls does not enforce batch limit when BatchConfig is null', function (): void {
        $console = createGoogleConsole();

        $result = $console->inspectBatchUrls('https://example.com/', [], []);

        expect($result->batchVerdict)->toBe(BatchVerdict::PASS);
    });

    it('inspectBatchUrls records soft failure for rate limited url', function (): void {
        $console = createGoogleConsole(createBatchConfig(maxRetries: 0));

        $urlInspectionResource = Mockery::mock(SearchConsoleService\Resource\UrlInspectionIndex::class);
        $urlInspectionResource->shouldReceive('inspect')
            ->andThrow(new GoogleServiceException('Quota exceeded', 429));

        $searchConsoleService = Mockery::mock(SearchConsoleService::class);
        $searchConsoleService->urlInspection_index = $urlInspectionResource;

        $reflection = new ReflectionClass($console);
        $property = $reflection->getProperty('searchConsoleService');
        $property->setValue($console, $searchConsoleService);

        $result = $console->inspectBatchUrls(
            'https://example.com/',
            ['https://example.com/rate-limited'],
        );

        expect($result->perUrlResults)->toHaveCount(1)
            ->and($result->perUrlResults['https://example.com/rate-limited']->status)->toBe(IndexingCheckStatus::UNKNOWN)
            ->and($result->perUrlResults['https://example.com/rate-limited']->failureType)->toBe(FailureType::SOFT)
            ->and($result->perUrlResults['https://example.com/rate-limited']->isSoftFailure())->toBeTrue()
            ->and($result->perUrlResults['https://example.com/rate-limited']->result->indexingCheckResult?->reasonCodes[0])
            ->toBe(IndexingCheckReasonCode::RATE_LIMITED);
    });

    it('inspectBatchUrls records soft failure for timeout', function (): void {
        $console = createGoogleConsole(createBatchConfig(maxRetries: 0));

        $urlInspectionResource = Mockery::mock(SearchConsoleService\Resource\UrlInspectionIndex::class);
        $urlInspectionResource->shouldReceive('inspect')
            ->andThrow(new GoogleServiceException('Gateway timeout', 504));

        $searchConsoleService = Mockery::mock(SearchConsoleService::class);
        $searchConsoleService->urlInspection_index = $urlInspectionResource;

        $reflection = new ReflectionClass($console);
        $property = $reflection->getProperty('searchConsoleService');
        $property->setValue($console, $searchConsoleService);

        $result = $console->inspectBatchUrls(
            'https://example.com/',
            ['https://example.com/timeout'],
        );

        expect($result->perUrlResults['https://example.com/timeout']->status)->toBe(IndexingCheckStatus::UNKNOWN)
            ->and($result->perUrlResults['https://example.com/timeout']->failureType)->toBe(FailureType::SOFT)
            ->and($result->perUrlResults['https://example.com/timeout']->result->indexingCheckResult?->reasonCodes[0])
            ->toBe(IndexingCheckReasonCode::TIMEOUT);
    });

    it('inspectBatchUrls records soft failure for server error with insufficient data', function (): void {
        $console = createGoogleConsole(createBatchConfig(maxRetries: 0));

        $urlInspectionResource = Mockery::mock(SearchConsoleService\Resource\UrlInspectionIndex::class);
        $urlInspectionResource->shouldReceive('inspect')
            ->andThrow(new GoogleServiceException('Internal server error', 500));

        $searchConsoleService = Mockery::mock(SearchConsoleService::class);
        $searchConsoleService->urlInspection_index = $urlInspectionResource;

        $reflection = new ReflectionClass($console);
        $property = $reflection->getProperty('searchConsoleService');
        $property->setValue($console, $searchConsoleService);

        $result = $console->inspectBatchUrls(
            'https://example.com/',
            ['https://example.com/error'],
        );

        expect($result->perUrlResults['https://example.com/error']->failureType)->toBe(FailureType::SOFT)
            ->and($result->perUrlResults['https://example.com/error']->result->indexingCheckResult?->reasonCodes[0])
            ->toBe(IndexingCheckReasonCode::INSUFFICIENT_DATA);
    });

    it('inspectBatchUrls retries on soft failure and succeeds', function (): void {
        $console = createGoogleConsole(createBatchConfig(maxRetries: 2));

        $indexStatus = Mockery::mock(IndexStatusInspectionResult::class);
        $indexStatus->shouldReceive('getVerdict')->andReturn('PASS');
        $indexStatus->shouldReceive('getCoverageState')->andReturn('Submitted and indexed');
        $indexStatus->shouldReceive('getRobotsTxtState')->andReturn('ALLOWED');
        $indexStatus->shouldReceive('getIndexingState')->andReturn('INDEXING_ALLOWED');
        $indexStatus->shouldReceive('getLastCrawlTime')->andReturn(null);
        $indexStatus->shouldReceive('getPageFetchState')->andReturn('SUCCESSFUL');
        $indexStatus->shouldReceive('getCrawledAs')->andReturn('MOBILE');
        $indexStatus->shouldReceive('getGoogleCanonical')->andReturn('https://example.com/retry');
        $indexStatus->shouldReceive('getUserCanonical')->andReturn('https://example.com/retry');

        $mobileUsability = Mockery::mock(MobileUsabilityInspectionResult::class);
        $mobileUsability->shouldReceive('getVerdict')->andReturn('PASS');
        $mobileUsability->shouldReceive('getIssues')->andReturn([]);

        $inspectionResult = Mockery::mock(GoogleUrlInspectionResult::class);
        $inspectionResult->shouldReceive('getInspectionResultLink')->andReturn('');
        $inspectionResult->shouldReceive('getIndexStatusResult')->andReturn($indexStatus);
        $inspectionResult->shouldReceive('getMobileUsabilityResult')->andReturn($mobileUsability);

        $inspectResponse = Mockery::mock(InspectUrlIndexResponse::class);
        $inspectResponse->shouldReceive('getInspectionResult')->andReturn($inspectionResult);

        $counter = new class () {

            private int $value = 0;

            public function increment(): void
            {
                $this->value++;
            }

            public function getValue(): int
            {
                return $this->value;
            }

        };
        $urlInspectionResource = Mockery::mock(SearchConsoleService\Resource\UrlInspectionIndex::class);
        $urlInspectionResource->shouldReceive('inspect')
            ->andReturnUsing(static function () use ($counter, $inspectResponse): InspectUrlIndexResponse {
                $counter->increment();

                if ($counter->getValue() <= 2) {
                    throw new GoogleServiceException('Quota exceeded', 429);
                }

                return $inspectResponse;
            });

        $searchConsoleService = Mockery::mock(SearchConsoleService::class);
        $searchConsoleService->urlInspection_index = $urlInspectionResource;

        $reflection = new ReflectionClass($console);
        $property = $reflection->getProperty('searchConsoleService');
        $property->setValue($console, $searchConsoleService);

        $result = $console->inspectBatchUrls(
            'https://example.com/',
            ['https://example.com/retry'],
        );

        expect($result->perUrlResults['https://example.com/retry']->status)->toBe(IndexingCheckStatus::INDEXED)
            ->and($result->perUrlResults['https://example.com/retry']->failureType)->toBeNull()
            ->and($counter->getValue())->toBe(3);
    });

    it('inspectBatchUrls throws hard failure for 403 forbidden', function (): void {
        $console = createGoogleConsole(createBatchConfig());

        $urlInspectionResource = Mockery::mock(SearchConsoleService\Resource\UrlInspectionIndex::class);
        $urlInspectionResource->shouldReceive('inspect')
            ->andThrow(new GoogleServiceException('Permission denied', 403));

        $searchConsoleService = Mockery::mock(SearchConsoleService::class);
        $searchConsoleService->urlInspection_index = $urlInspectionResource;

        $reflection = new ReflectionClass($console);
        $property = $reflection->getProperty('searchConsoleService');
        $property->setValue($console, $searchConsoleService);

        $console->inspectBatchUrls(
            'https://example.com/',
            ['https://example.com/forbidden'],
        );
    })->throws(GoogleConsoleFailure::class, 'URL inspection failed');

    it('inspectBatchUrls throws hard failure without batch config', function (): void {
        $console = createGoogleConsole();

        $urlInspectionResource = Mockery::mock(SearchConsoleService\Resource\UrlInspectionIndex::class);
        $urlInspectionResource->shouldReceive('inspect')
            ->andThrow(new GoogleServiceException('Quota exceeded', 429));

        $searchConsoleService = Mockery::mock(SearchConsoleService::class);
        $searchConsoleService->urlInspection_index = $urlInspectionResource;

        $reflection = new ReflectionClass($console);
        $property = $reflection->getProperty('searchConsoleService');
        $property->setValue($console, $searchConsoleService);

        $console->inspectBatchUrls(
            'https://example.com/',
            ['https://example.com/page'],
        );
    })->throws(GoogleConsoleFailure::class, 'URL inspection failed');

    it('inspectBatchUrls applies cooldown between retries', function (): void {
        $tracker = new class () {

            /**
             * @var array<int, int>
             */
            private array $calls = [];

            public function record(int $seconds): void
            {
                $this->calls[] = $seconds;
            }

            /**
             * @return array<int, int>
             */
            public function getCalls(): array
            {
                return $this->calls;
            }

        };
        $sleepFn = static function (int $seconds) use ($tracker): void {
            $tracker->record($seconds);
        };
        $batchConfig = new BatchConfig(
            maxBatchSize: 10,
            cooldownSeconds: 3,
            maxRetries: 2,
            sleepFunction: $sleepFn,
            backoff: new Backoff(baseSeconds: 3, useJitter: false, sleepFunction: $sleepFn),
        );
        $console = createGoogleConsole($batchConfig);

        $urlInspectionResource = Mockery::mock(SearchConsoleService\Resource\UrlInspectionIndex::class);
        $urlInspectionResource->shouldReceive('inspect')
            ->andThrow(new GoogleServiceException('Quota exceeded', 429));

        $searchConsoleService = Mockery::mock(SearchConsoleService::class);
        $searchConsoleService->urlInspection_index = $urlInspectionResource;

        $reflection = new ReflectionClass($console);
        $property = $reflection->getProperty('searchConsoleService');
        $property->setValue($console, $searchConsoleService);

        $result = $console->inspectBatchUrls(
            'https://example.com/',
            ['https://example.com/cooldown'],
        );

        expect($tracker->getCalls())->toBe([3, 6])
            ->and($result->perUrlResults['https://example.com/cooldown']->isSoftFailure())->toBeTrue();
    });

    it('inspectBatchUrls handles mix of successful and soft failure urls', function (): void {
        $console = createGoogleConsole(createBatchConfig(maxRetries: 0));

        $indexStatus = Mockery::mock(IndexStatusInspectionResult::class);
        $indexStatus->shouldReceive('getVerdict')->andReturn('PASS');
        $indexStatus->shouldReceive('getCoverageState')->andReturn('Submitted and indexed');
        $indexStatus->shouldReceive('getRobotsTxtState')->andReturn('ALLOWED');
        $indexStatus->shouldReceive('getIndexingState')->andReturn('INDEXING_ALLOWED');
        $indexStatus->shouldReceive('getLastCrawlTime')->andReturn(null);
        $indexStatus->shouldReceive('getPageFetchState')->andReturn('SUCCESSFUL');
        $indexStatus->shouldReceive('getCrawledAs')->andReturn('MOBILE');
        $indexStatus->shouldReceive('getGoogleCanonical')->andReturn('https://example.com/ok');
        $indexStatus->shouldReceive('getUserCanonical')->andReturn('https://example.com/ok');

        $mobileUsability = Mockery::mock(MobileUsabilityInspectionResult::class);
        $mobileUsability->shouldReceive('getVerdict')->andReturn('PASS');
        $mobileUsability->shouldReceive('getIssues')->andReturn([]);

        $inspectionResult = Mockery::mock(GoogleUrlInspectionResult::class);
        $inspectionResult->shouldReceive('getInspectionResultLink')->andReturn('');
        $inspectionResult->shouldReceive('getIndexStatusResult')->andReturn($indexStatus);
        $inspectionResult->shouldReceive('getMobileUsabilityResult')->andReturn($mobileUsability);

        $inspectResponse = Mockery::mock(InspectUrlIndexResponse::class);
        $inspectResponse->shouldReceive('getInspectionResult')->andReturn($inspectionResult);

        $urlInspectionResource = Mockery::mock(SearchConsoleService\Resource\UrlInspectionIndex::class);
        $urlInspectionResource->shouldReceive('inspect')
            ->andReturnUsing(static function (InspectUrlIndexRequest $request) use ($inspectResponse): InspectUrlIndexResponse {
                if ($request->getInspectionUrl() === 'https://example.com/fail') {
                    throw new GoogleServiceException('Quota exceeded', 429);
                }

                return $inspectResponse;
            });

        $searchConsoleService = Mockery::mock(SearchConsoleService::class);
        $searchConsoleService->urlInspection_index = $urlInspectionResource;

        $reflection = new ReflectionClass($console);
        $property = $reflection->getProperty('searchConsoleService');
        $property->setValue($console, $searchConsoleService);

        $result = $console->inspectBatchUrls(
            'https://example.com/',
            ['https://example.com/ok', 'https://example.com/fail'],
        );

        expect($result->perUrlResults)->toHaveCount(2)
            ->and($result->perUrlResults['https://example.com/ok']->status)->toBe(IndexingCheckStatus::INDEXED)
            ->and($result->perUrlResults['https://example.com/ok']->failureType)->toBeNull()
            ->and($result->perUrlResults['https://example.com/fail']->status)->toBe(IndexingCheckStatus::UNKNOWN)
            ->and($result->perUrlResults['https://example.com/fail']->failureType)->toBe(FailureType::SOFT)
            ->and($result->aggregation->indexedCount)->toBe(1)
            ->and($result->aggregation->unknownCount)->toBe(1);
    });

    it('inspectBatchUrls maps 408 to timeout reason code', function (): void {
        $console = createGoogleConsole(createBatchConfig(maxRetries: 0));

        $urlInspectionResource = Mockery::mock(SearchConsoleService\Resource\UrlInspectionIndex::class);
        $urlInspectionResource->shouldReceive('inspect')
            ->andThrow(new GoogleServiceException('Request timeout', 408));

        $searchConsoleService = Mockery::mock(SearchConsoleService::class);
        $searchConsoleService->urlInspection_index = $urlInspectionResource;

        $reflection = new ReflectionClass($console);
        $property = $reflection->getProperty('searchConsoleService');
        $property->setValue($console, $searchConsoleService);

        $result = $console->inspectBatchUrls(
            'https://example.com/',
            ['https://example.com/timeout'],
        );

        expect($result->perUrlResults['https://example.com/timeout']->result->indexingCheckResult?->reasonCodes[0])
            ->toBe(IndexingCheckReasonCode::TIMEOUT);
    });

    it('compareIndexingRuns returns changes and deltas between two runs', function (): void {
        $indexedResult = UrlInspectionResult::fromApiResponse([
            'indexStatusResult' => ['verdict' => 'PASS', 'coverageState' => 'Indexed'],
            'mobileUsabilityResult' => ['verdict' => 'PASS', 'issues' => []],
        ]);
        $notIndexedResult = UrlInspectionResult::fromApiResponse([
            'indexStatusResult' => ['verdict' => 'FAIL', 'coverageState' => ''],
            'mobileUsabilityResult' => ['verdict' => 'PASS', 'issues' => []],
        ]);
        $url = 'https://example.com/page';
        $aggregation = new BatchAggregation(0, 0, 0, []);
        $previous = new BatchUrlInspectionResult(
            perUrlResults: [$url => new PerUrlInspectionResult($url, IndexingCheckStatus::INDEXED, $indexedResult)],
            aggregation: $aggregation,
            criticalUrlResults: [],
            batchVerdict: BatchVerdict::PASS,
        );
        $current = new BatchUrlInspectionResult(
            perUrlResults: [$url => new PerUrlInspectionResult($url, IndexingCheckStatus::NOT_INDEXED, $notIndexedResult)],
            aggregation: $aggregation,
            criticalUrlResults: [],
            batchVerdict: BatchVerdict::FAIL,
        );

        $console = createGoogleConsole();
        $comparison = $console->compareIndexingRuns($previous, $current);

        expect($comparison->changes)->toHaveCount(1)
            ->and($comparison->changes[0]->changeType)->toBe(IndexingChangeType::DROPPED_FROM_INDEX)
            ->and($comparison->indexedDelta)->toBe(-1)
            ->and($comparison->notIndexedDelta)->toBe(1);
    });
});
