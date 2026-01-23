<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\Command\RequestIndexingCommand;
use Pekral\GoogleConsole\ConsoleContract;
use Pekral\GoogleConsole\DTO\IndexingResult;
use Pekral\GoogleConsole\Enum\IndexingNotificationType;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

describe(RequestIndexingCommand::class, function (): void {

    it('requests url indexing successfully', function (): void {
        $result = new IndexingResult(
            url: 'https://example.com/page',
            type: IndexingNotificationType::URL_UPDATED,
            notifyTime: new DateTimeImmutable('2024-01-15 10:30:00'),
        );

        $googleConsole = Mockery::mock(ConsoleContract::class);
        $googleConsole->shouldReceive('requestIndexing')
            ->with('https://example.com/page', IndexingNotificationType::URL_UPDATED)
            ->andReturn($result);

        $command = new RequestIndexingCommand();
        $command->setGoogleConsole($googleConsole);

        $tester = new CommandTester($command);
        $tester->execute([
            'url' => 'https://example.com/page',
            '--credentials' => '/path/to/credentials.json',
        ]);

        expect($tester->getStatusCode())->toBe(Command::SUCCESS)
            ->and($tester->getDisplay())->toContain('https://example.com/page')
            ->and($tester->getDisplay())->toContain('URL_UPDATED')
            ->and($tester->getDisplay())->toContain('Indexing request submitted successfully');
    });

    it('requests url deletion successfully', function (): void {
        $result = new IndexingResult(
            url: 'https://example.com/page',
            type: IndexingNotificationType::URL_DELETED,
            notifyTime: new DateTimeImmutable('2024-01-15 10:30:00'),
        );

        $googleConsole = Mockery::mock(ConsoleContract::class);
        $googleConsole->shouldReceive('requestIndexing')
            ->with('https://example.com/page', IndexingNotificationType::URL_DELETED)
            ->andReturn($result);

        $command = new RequestIndexingCommand();
        $command->setGoogleConsole($googleConsole);

        $tester = new CommandTester($command);
        $tester->execute([
            'url' => 'https://example.com/page',
            '--credentials' => '/path/to/credentials.json',
            '--delete' => true,
        ]);

        expect($tester->getStatusCode())->toBe(Command::SUCCESS)
            ->and($tester->getDisplay())->toContain('URL_DELETED')
            ->and($tester->getDisplay())->toContain('URL removal request submitted successfully');
    });

    it('displays result with null notify time', function (): void {
        $result = new IndexingResult(
            url: 'https://example.com/page',
            type: IndexingNotificationType::URL_UPDATED,
        );

        $googleConsole = Mockery::mock(ConsoleContract::class);
        $googleConsole->shouldReceive('requestIndexing')->andReturn($result);

        $command = new RequestIndexingCommand();
        $command->setGoogleConsole($googleConsole);

        $tester = new CommandTester($command);
        $tester->execute([
            'url' => 'https://example.com/page',
            '--credentials' => '/path/to/credentials.json',
        ]);

        expect($tester->getStatusCode())->toBe(Command::SUCCESS)
            ->and($tester->getDisplay())->toContain('N/A');
    });

    it('has correct command name and description', function (): void {
        $command = new RequestIndexingCommand();

        expect($command->getName())->toBe('pekral:google-request-indexing')
            ->and($command->getDescription())->toBe('Requests Google to index or remove a URL');
    });

    it('requires url argument', function (): void {
        $command = new RequestIndexingCommand();
        $definition = $command->getDefinition();

        expect($definition->hasArgument('url'))->toBeTrue()
            ->and($definition->getArgument('url')->isRequired())->toBeTrue();
    });

    it('has delete option', function (): void {
        $command = new RequestIndexingCommand();
        $definition = $command->getDefinition();

        expect($definition->hasOption('delete'))->toBeTrue()
            ->and($definition->getOption('delete')->getShortcut())->toBe('d');
    });

    it('outputs json when json option is set', function (): void {
        $result = new IndexingResult(
            url: 'https://example.com/page',
            type: IndexingNotificationType::URL_UPDATED,
            notifyTime: new DateTimeImmutable('2024-01-15 10:30:00'),
        );

        $googleConsole = Mockery::mock(ConsoleContract::class);
        $googleConsole->shouldReceive('requestIndexing')
            ->with('https://example.com/page', IndexingNotificationType::URL_UPDATED)
            ->andReturn($result);

        $command = new RequestIndexingCommand();
        $command->setGoogleConsole($googleConsole);

        $tester = new CommandTester($command);
        $tester->execute([
            'url' => 'https://example.com/page',
            '--credentials' => '/path/to/credentials.json',
            '--json' => true,
        ]);

        $output = $tester->getDisplay();
        $decoded = json_decode($output, true, 512, JSON_THROW_ON_ERROR);

        expect($tester->getStatusCode())->toBe(Command::SUCCESS)
            ->and($decoded)->toBeArray()
            ->and($decoded['url'])->toBe('https://example.com/page')
            ->and($decoded['type'])->toBe('URL_UPDATED')
            ->and($decoded['notifyTime'])->toBe('2024-01-15 10:30:00');
    });

    it('outputs json with delete option', function (): void {
        $result = new IndexingResult(
            url: 'https://example.com/page',
            type: IndexingNotificationType::URL_DELETED,
        );

        $googleConsole = Mockery::mock(ConsoleContract::class);
        $googleConsole->shouldReceive('requestIndexing')
            ->with('https://example.com/page', IndexingNotificationType::URL_DELETED)
            ->andReturn($result);

        $command = new RequestIndexingCommand();
        $command->setGoogleConsole($googleConsole);

        $tester = new CommandTester($command);
        $tester->execute([
            'url' => 'https://example.com/page',
            '--credentials' => '/path/to/credentials.json',
            '--json' => true,
            '--delete' => true,
        ]);

        $output = $tester->getDisplay();
        $decoded = json_decode($output, true, 512, JSON_THROW_ON_ERROR);

        expect($tester->getStatusCode())->toBe(Command::SUCCESS)
            ->and($decoded['type'])->toBe('URL_DELETED')
            ->and($decoded['notifyTime'])->toBeNull();
    });
});
