<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\Command\SearchAnalyticsCommand;
use Pekral\GoogleConsole\ConsoleContract;
use Pekral\GoogleConsole\DTO\SearchAnalyticsRow;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

describe(SearchAnalyticsCommand::class, function (): void {

    it('displays search analytics data in table', function (): void {
        $rows = [
            new SearchAnalyticsRow(['query' => 'test query'], 100.0, 1000.0, 0.1, 5.5),
            new SearchAnalyticsRow(['query' => 'another query'], 50.0, 500.0, 0.1, 3.0),
        ];

        $googleConsole = Mockery::mock(ConsoleContract::class);
        $googleConsole->shouldReceive('getSearchAnalytics')->andReturn($rows);

        $command = new SearchAnalyticsCommand();
        $command->setGoogleConsole($googleConsole);

        $tester = new CommandTester($command);
        $tester->execute([
            'site-url' => 'https://example.com/',
            '--credentials' => '/path/to/credentials.json',
            '--start-date' => '2024-01-01',
            '--end-date' => '2024-01-31',
        ]);

        expect($tester->getStatusCode())->toBe(Command::SUCCESS)
            ->and($tester->getDisplay())->toContain('test query')
            ->and($tester->getDisplay())->toContain('another query')
            ->and($tester->getDisplay())->toContain('100')
            ->and($tester->getDisplay())->toContain('2 row(s)');
    });

    it('shows warning when no data found', function (): void {
        $googleConsole = Mockery::mock(ConsoleContract::class);
        $googleConsole->shouldReceive('getSearchAnalytics')->andReturn([]);

        $command = new SearchAnalyticsCommand();
        $command->setGoogleConsole($googleConsole);

        $tester = new CommandTester($command);
        $tester->execute([
            'site-url' => 'https://example.com/',
            '--credentials' => '/path/to/credentials.json',
        ]);

        expect($tester->getStatusCode())->toBe(Command::SUCCESS)
            ->and($tester->getDisplay())->toContain('No search analytics data found');
    });

    it('has correct command name and description', function (): void {
        $command = new SearchAnalyticsCommand();

        expect($command->getName())->toBe('pekral:google-analytics-search')
            ->and($command->getDescription())->toBe('Retrieves search analytics data for a site from Google Search Console');
    });

    it('supports multiple dimensions', function (): void {
        $rows = [
            new SearchAnalyticsRow(['query' => 'test', 'page' => '/page1'], 100.0, 1000.0, 0.1, 5.5),
        ];

        $googleConsole = Mockery::mock(ConsoleContract::class);
        $googleConsole->shouldReceive('getSearchAnalytics')->andReturn($rows);

        $command = new SearchAnalyticsCommand();
        $command->setGoogleConsole($googleConsole);

        $tester = new CommandTester($command);
        $tester->execute([
            'site-url' => 'https://example.com/',
            '--credentials' => '/path/to/credentials.json',
            '--dimensions' => 'query,page',
        ]);

        expect($tester->getStatusCode())->toBe(Command::SUCCESS)
            ->and($tester->getDisplay())->toContain('test')
            ->and($tester->getDisplay())->toContain('/page1');
    });

    it('has required options with defaults', function (): void {
        $command = new SearchAnalyticsCommand();
        $definition = $command->getDefinition();

        expect($definition->hasOption('start-date'))->toBeTrue()
            ->and($definition->hasOption('end-date'))->toBeTrue()
            ->and($definition->hasOption('dimensions'))->toBeTrue()
            ->and($definition->hasOption('row-limit'))->toBeTrue()
            ->and($definition->hasOption('start-row'))->toBeTrue()
            ->and($definition->getOption('dimensions')->getDefault())->toBe('query')
            ->and($definition->getOption('row-limit')->getDefault())->toBe('100')
            ->and($definition->getOption('start-row')->getDefault())->toBe('0');
    });

    it('formats CTR as percentage', function (): void {
        $rows = [
            new SearchAnalyticsRow(['query' => 'test'], 100.0, 1000.0, 0.1234, 5.5),
        ];

        $googleConsole = Mockery::mock(ConsoleContract::class);
        $googleConsole->shouldReceive('getSearchAnalytics')->andReturn($rows);

        $command = new SearchAnalyticsCommand();
        $command->setGoogleConsole($googleConsole);

        $tester = new CommandTester($command);
        $tester->execute([
            'site-url' => 'https://example.com/',
            '--credentials' => '/path/to/credentials.json',
        ]);

        expect($tester->getDisplay())->toContain('12.34%');
    });

    it('outputs json when json option is set', function (): void {
        $rows = [
            new SearchAnalyticsRow(['query' => 'test query'], 100.0, 1000.0, 0.1, 5.5),
        ];

        $googleConsole = Mockery::mock(ConsoleContract::class);
        $googleConsole->shouldReceive('getSearchAnalytics')->andReturn($rows);

        $command = new SearchAnalyticsCommand();
        $command->setGoogleConsole($googleConsole);

        $tester = new CommandTester($command);
        $tester->execute([
            'site-url' => 'https://example.com/',
            '--credentials' => '/path/to/credentials.json',
            '--start-date' => '2024-01-01',
            '--end-date' => '2024-01-31',
            '--json' => true,
        ]);

        $output = $tester->getDisplay();
        $decoded = json_decode($output, true, 512, JSON_THROW_ON_ERROR);

        expect($tester->getStatusCode())->toBe(Command::SUCCESS)
            ->and($decoded)->toBeArray()
            ->and($decoded['siteUrl'])->toBe('https://example.com/')
            ->and($decoded['startDate'])->toBe('2024-01-01')
            ->and($decoded['endDate'])->toBe('2024-01-31')
            ->and($decoded['count'])->toBe(1)
            ->and((float) $decoded['rows'][0]['clicks'])->toBe(100.0)
            ->and($decoded['rows'][0]['keys']['query'])->toBe('test query');
    });

    it('outputs empty json when no data found with json option', function (): void {
        $googleConsole = Mockery::mock(ConsoleContract::class);
        $googleConsole->shouldReceive('getSearchAnalytics')->andReturn([]);

        $command = new SearchAnalyticsCommand();
        $command->setGoogleConsole($googleConsole);

        $tester = new CommandTester($command);
        $tester->execute([
            'site-url' => 'https://example.com/',
            '--credentials' => '/path/to/credentials.json',
            '--json' => true,
        ]);

        $output = $tester->getDisplay();
        $decoded = json_decode($output, true, 512, JSON_THROW_ON_ERROR);

        expect($tester->getStatusCode())->toBe(Command::SUCCESS)
            ->and($decoded['count'])->toBe(0)
            ->and($decoded['rows'])->toBe([]);
    });
});
