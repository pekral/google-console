<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\Command\GetSiteCommand;
use Pekral\GoogleConsole\ConsoleContract;
use Pekral\GoogleConsole\DTO\Site;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

describe(GetSiteCommand::class, function (): void {

    it('displays site information', function (): void {
        $site = new Site('https://example.com/', 'siteOwner');

        $googleConsole = Mockery::mock(ConsoleContract::class);
        $googleConsole->shouldReceive('getSite')
            ->with('https://example.com/')
            ->andReturn($site);

        $command = new GetSiteCommand();
        $command->setGoogleConsole($googleConsole);

        $tester = new CommandTester($command);
        $tester->execute([
            'site-url' => 'https://example.com/',
            '--credentials' => '/path/to/credentials.json',
        ]);

        expect($tester->getStatusCode())->toBe(Command::SUCCESS)
            ->and($tester->getDisplay())->toContain('example.com')
            ->and($tester->getDisplay())->toContain('siteOwner');
    });

    it('has correct command name and description', function (): void {
        $command = new GetSiteCommand();

        expect($command->getName())->toBe('pekral:google-site-get')
            ->and($command->getDescription())->toBe('Retrieves information about a specific site from Google Search Console');
    });

    it('displays non-owner site correctly', function (): void {
        $site = new Site('https://example.com/', 'siteFullUser');

        $googleConsole = Mockery::mock(ConsoleContract::class);
        $googleConsole->shouldReceive('getSite')
            ->with('https://example.com/')
            ->andReturn($site);

        $command = new GetSiteCommand();
        $command->setGoogleConsole($googleConsole);

        $tester = new CommandTester($command);
        $tester->execute([
            'site-url' => 'https://example.com/',
            '--credentials' => '/path/to/credentials.json',
        ]);

        expect($tester->getStatusCode())->toBe(Command::SUCCESS)
            ->and($tester->getDisplay())->toContain('siteFullUser');
    });

    it('requires site-url argument', function (): void {
        $command = new GetSiteCommand();
        $definition = $command->getDefinition();

        expect($definition->hasArgument('site-url'))->toBeTrue()
            ->and($definition->getArgument('site-url')->isRequired())->toBeTrue();
    });

    it('outputs json when json option is set', function (): void {
        $site = new Site('https://example.com/', 'siteOwner');

        $googleConsole = Mockery::mock(ConsoleContract::class);
        $googleConsole->shouldReceive('getSite')
            ->with('https://example.com/')
            ->andReturn($site);

        $command = new GetSiteCommand();
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
            ->and($decoded)->toBeArray()
            ->and($decoded['siteUrl'])->toBe('https://example.com/')
            ->and($decoded['permissionLevel'])->toBe('siteOwner')
            ->and($decoded['isOwner'])->toBeTrue()
            ->and($decoded['hasFullAccess'])->toBeTrue();
    });
});
