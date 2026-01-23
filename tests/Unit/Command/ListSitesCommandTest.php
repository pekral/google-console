<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\Command\ListSitesCommand;
use Pekral\GoogleConsole\ConsoleContract;
use Pekral\GoogleConsole\DTO\Site;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

describe(ListSitesCommand::class, function (): void {

    it('lists sites in table format', function (): void {
        $sites = [
            new Site('https://example.com/', 'siteOwner'),
            new Site('https://example.org/', 'siteFullUser'),
        ];

        $googleConsole = Mockery::mock(ConsoleContract::class);
        $googleConsole->shouldReceive('getSiteList')->andReturn($sites);

        $command = new ListSitesCommand();
        $command->setGoogleConsole($googleConsole);

        $tester = new CommandTester($command);
        $tester->execute(['--credentials' => '/path/to/credentials.json']);

        expect($tester->getStatusCode())->toBe(Command::SUCCESS)
            ->and($tester->getDisplay())->toContain('example.com')
            ->and($tester->getDisplay())->toContain('example.org')
            ->and($tester->getDisplay())->toContain('siteOwner')
            ->and($tester->getDisplay())->toContain('siteFullUser')
            ->and($tester->getDisplay())->toContain('2 site(s)');
    });

    it('shows warning when no sites found', function (): void {
        $googleConsole = Mockery::mock(ConsoleContract::class);
        $googleConsole->shouldReceive('getSiteList')->andReturn([]);

        $command = new ListSitesCommand();
        $command->setGoogleConsole($googleConsole);

        $tester = new CommandTester($command);
        $tester->execute(['--credentials' => '/path/to/credentials.json']);

        expect($tester->getStatusCode())->toBe(Command::SUCCESS)
            ->and($tester->getDisplay())->toContain('No sites found');
    });

    it('has correct command name and description', function (): void {
        $command = new ListSitesCommand();

        expect($command->getName())->toBe('pekral:google-sites-list')
            ->and($command->getDescription())->toBe('Lists all sites registered in Google Search Console');
    });

    it('displays owner status correctly', function (): void {
        $sites = [
            new Site('https://owner-site.com/', 'siteOwner'),
            new Site('https://restricted-site.com/', 'siteRestrictedUser'),
        ];

        $googleConsole = Mockery::mock(ConsoleContract::class);
        $googleConsole->shouldReceive('getSiteList')->andReturn($sites);

        $command = new ListSitesCommand();
        $command->setGoogleConsole($googleConsole);

        $tester = new CommandTester($command);
        $tester->execute(['--credentials' => '/path/to/credentials.json']);

        $display = $tester->getDisplay();

        expect($display)->toContain('Yes')
            ->and($display)->toContain('No');
    });

    it('outputs json when json option is set', function (): void {
        $sites = [
            new Site('https://example.com/', 'siteOwner'),
        ];

        $googleConsole = Mockery::mock(ConsoleContract::class);
        $googleConsole->shouldReceive('getSiteList')->andReturn($sites);

        $command = new ListSitesCommand();
        $command->setGoogleConsole($googleConsole);

        $tester = new CommandTester($command);
        $tester->execute(['--credentials' => '/path/to/credentials.json', '--json' => true]);

        $output = $tester->getDisplay();
        $decoded = json_decode($output, true, 512, JSON_THROW_ON_ERROR);

        expect($tester->getStatusCode())->toBe(Command::SUCCESS)
            ->and($decoded)->toBeArray()
            ->and($decoded['count'])->toBe(1)
            ->and($decoded['sites'][0]['siteUrl'])->toBe('https://example.com/')
            ->and($decoded['sites'][0]['isOwner'])->toBeTrue();
    });

    it('outputs empty json when no sites found with json option', function (): void {
        $googleConsole = Mockery::mock(ConsoleContract::class);
        $googleConsole->shouldReceive('getSiteList')->andReturn([]);

        $command = new ListSitesCommand();
        $command->setGoogleConsole($googleConsole);

        $tester = new CommandTester($command);
        $tester->execute(['--credentials' => '/path/to/credentials.json', '--json' => true]);

        $output = $tester->getDisplay();
        $decoded = json_decode($output, true, 512, JSON_THROW_ON_ERROR);

        expect($tester->getStatusCode())->toBe(Command::SUCCESS)
            ->and($decoded['count'])->toBe(0)
            ->and($decoded['sites'])->toBe([]);
    });
});
