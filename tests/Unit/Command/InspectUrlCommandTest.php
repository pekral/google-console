<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\Command\InspectUrlCommand;
use Pekral\GoogleConsole\ConsoleContract;
use Pekral\GoogleConsole\DTO\UrlInspectionResult;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

describe(InspectUrlCommand::class, function (): void {

    it('displays url inspection result', function (): void {
        $result = new UrlInspectionResult(
            inspectionResultLink: 'https://search.google.com/search-console/inspect',
            indexStatusResult: 'PASS',
            verdict: 'PASS',
            coverageState: 'Submitted and indexed',
            robotsTxtState: 'ALLOWED',
            indexingState: 'INDEXING_ALLOWED',
            lastCrawlTime: new DateTimeImmutable('2024-01-15 10:30:00'),
            pageFetchState: 'SUCCESSFUL',
            crawledAs: 'MOBILE',
            googleCanonical: 'https://example.com/page',
            userCanonical: 'https://example.com/page',
            isMobileFriendly: true,
            mobileUsabilityIssue: null,
        );

        $googleConsole = Mockery::mock(ConsoleContract::class);
        $googleConsole->shouldReceive('inspectUrl')
            ->with('https://example.com/', 'https://example.com/page')
            ->andReturn($result);

        $command = new InspectUrlCommand();
        $command->setGoogleConsole($googleConsole);

        $tester = new CommandTester($command);
        $tester->execute([
            'site-url' => 'https://example.com/',
            'inspection-url' => 'https://example.com/page',
            '--credentials' => '/path/to/credentials.json',
        ]);

        expect($tester->getStatusCode())->toBe(Command::SUCCESS)
            ->and($tester->getDisplay())->toContain('PASS')
            ->and($tester->getDisplay())->toContain('Submitted and indexed')
            ->and($tester->getDisplay())->toContain('MOBILE')
            ->and($tester->getDisplay())->toContain('Yes');
    });

    it('displays result with null values', function (): void {
        $result = new UrlInspectionResult(
            inspectionResultLink: '',
            indexStatusResult: 'VERDICT_UNSPECIFIED',
            verdict: 'VERDICT_UNSPECIFIED',
            coverageState: '',
            robotsTxtState: '',
            indexingState: '',
            lastCrawlTime: null,
            pageFetchState: '',
            crawledAs: null,
            googleCanonical: null,
            userCanonical: null,
            isMobileFriendly: false,
            mobileUsabilityIssue: null,
        );

        $googleConsole = Mockery::mock(ConsoleContract::class);
        $googleConsole->shouldReceive('inspectUrl')->andReturn($result);

        $command = new InspectUrlCommand();
        $command->setGoogleConsole($googleConsole);

        $tester = new CommandTester($command);
        $tester->execute([
            'site-url' => 'https://example.com/',
            'inspection-url' => 'https://example.com/page',
            '--credentials' => '/path/to/credentials.json',
        ]);

        expect($tester->getStatusCode())->toBe(Command::SUCCESS)
            ->and($tester->getDisplay())->toContain('N/A')
            ->and($tester->getDisplay())->toContain('None');
    });

    it('has correct command name and description', function (): void {
        $command = new InspectUrlCommand();

        expect($command->getName())->toBe('pekral:google-url-inspect')
            ->and($command->getDescription())->toBe('Inspects a URL to check its indexing status and mobile usability');
    });

    it('requires both site-url and inspection-url arguments', function (): void {
        $command = new InspectUrlCommand();
        $definition = $command->getDefinition();

        expect($definition->hasArgument('site-url'))->toBeTrue()
            ->and($definition->getArgument('site-url')->isRequired())->toBeTrue()
            ->and($definition->hasArgument('inspection-url'))->toBeTrue()
            ->and($definition->getArgument('inspection-url')->isRequired())->toBeTrue();
    });

    it('displays mobile usability issue', function (): void {
        $result = new UrlInspectionResult(
            inspectionResultLink: 'https://search.google.com/search-console/inspect',
            indexStatusResult: 'PASS',
            verdict: 'PASS',
            coverageState: 'Submitted and indexed',
            robotsTxtState: 'ALLOWED',
            indexingState: 'INDEXING_ALLOWED',
            lastCrawlTime: new DateTimeImmutable('2024-01-15 10:30:00'),
            pageFetchState: 'SUCCESSFUL',
            crawledAs: 'MOBILE',
            googleCanonical: 'https://example.com/page',
            userCanonical: 'https://example.com/page',
            isMobileFriendly: false,
            mobileUsabilityIssue: 'TEXT_TOO_SMALL',
        );

        $googleConsole = Mockery::mock(ConsoleContract::class);
        $googleConsole->shouldReceive('inspectUrl')->andReturn($result);

        $command = new InspectUrlCommand();
        $command->setGoogleConsole($googleConsole);

        $tester = new CommandTester($command);
        $tester->execute([
            'site-url' => 'https://example.com/',
            'inspection-url' => 'https://example.com/page',
            '--credentials' => '/path/to/credentials.json',
        ]);

        expect($tester->getDisplay())->toContain('TEXT_TOO_SMALL')
            ->and($tester->getDisplay())->toContain('No');
    });

    it('displays inspection result link', function (): void {
        $result = new UrlInspectionResult(
            inspectionResultLink: 'https://search.google.com/search-console/inspect?resource_id=test',
            indexStatusResult: 'PASS',
            verdict: 'PASS',
            coverageState: 'Submitted and indexed',
            robotsTxtState: 'ALLOWED',
            indexingState: 'INDEXING_ALLOWED',
            lastCrawlTime: null,
            pageFetchState: 'SUCCESSFUL',
            crawledAs: 'MOBILE',
            googleCanonical: null,
            userCanonical: null,
            isMobileFriendly: true,
            mobileUsabilityIssue: null,
        );

        $googleConsole = Mockery::mock(ConsoleContract::class);
        $googleConsole->shouldReceive('inspectUrl')->andReturn($result);

        $command = new InspectUrlCommand();
        $command->setGoogleConsole($googleConsole);

        $tester = new CommandTester($command);
        $tester->execute([
            'site-url' => 'https://example.com/',
            'inspection-url' => 'https://example.com/page',
            '--credentials' => '/path/to/credentials.json',
        ]);

        expect($tester->getDisplay())->toContain('Full report')
            ->and($tester->getDisplay())->toContain('search.google.com');
    });

    it('outputs json when json option is set', function (): void {
        $result = new UrlInspectionResult(
            inspectionResultLink: 'https://search.google.com/search-console/inspect',
            indexStatusResult: 'PASS',
            verdict: 'PASS',
            coverageState: 'Submitted and indexed',
            robotsTxtState: 'ALLOWED',
            indexingState: 'INDEXING_ALLOWED',
            lastCrawlTime: new DateTimeImmutable('2024-01-15 10:30:00'),
            pageFetchState: 'SUCCESSFUL',
            crawledAs: 'MOBILE',
            googleCanonical: 'https://example.com/page',
            userCanonical: 'https://example.com/page',
            isMobileFriendly: true,
            mobileUsabilityIssue: null,
        );

        $googleConsole = Mockery::mock(ConsoleContract::class);
        $googleConsole->shouldReceive('inspectUrl')
            ->with('https://example.com/', 'https://example.com/page')
            ->andReturn($result);

        $command = new InspectUrlCommand();
        $command->setGoogleConsole($googleConsole);

        $tester = new CommandTester($command);
        $tester->execute([
            'site-url' => 'https://example.com/',
            'inspection-url' => 'https://example.com/page',
            '--credentials' => '/path/to/credentials.json',
            '--json' => true,
        ]);

        $output = $tester->getDisplay();
        $decoded = json_decode($output, true, 512, JSON_THROW_ON_ERROR);

        expect($tester->getStatusCode())->toBe(Command::SUCCESS)
            ->and($decoded)->toBeArray()
            ->and($decoded['siteUrl'])->toBe('https://example.com/')
            ->and($decoded['inspectionUrl'])->toBe('https://example.com/page')
            ->and($decoded['verdict'])->toBe('PASS')
            ->and($decoded['isIndexed'])->toBeTrue()
            ->and($decoded['isMobileFriendly'])->toBeTrue();
    });
});
