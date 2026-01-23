<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\Command\Output\ConsoleOutput;
use Symfony\Component\Console\Output\BufferedOutput;

describe(ConsoleOutput::class, function (): void {

    it('renders header', function (): void {
        $output = new BufferedOutput();
        $consoleOutput = new ConsoleOutput($output);

        $consoleOutput->header('Test Header');

        expect($output->fetch())->toContain('Test Header');
    });

    it('renders section', function (): void {
        $output = new BufferedOutput();
        $consoleOutput = new ConsoleOutput($output);

        $consoleOutput->section('Test Section');

        expect($output->fetch())->toContain('Test Section');
    });

    it('renders success message', function (): void {
        $output = new BufferedOutput();
        $consoleOutput = new ConsoleOutput($output);

        $consoleOutput->success('Success message');

        expect($output->fetch())->toContain('Success message');
    });

    it('renders warning message', function (): void {
        $output = new BufferedOutput();
        $consoleOutput = new ConsoleOutput($output);

        $consoleOutput->warning('Warning message');

        expect($output->fetch())->toContain('Warning message');
    });

    it('renders info message', function (): void {
        $output = new BufferedOutput();
        $consoleOutput = new ConsoleOutput($output);

        $consoleOutput->info('Info message');

        expect($output->fetch())->toContain('Info message');
    });

    it('renders error message', function (): void {
        $output = new BufferedOutput();
        $consoleOutput = new ConsoleOutput($output);

        $consoleOutput->error('Error message');

        expect($output->fetch())->toContain('Error message');
    });

    it('renders table', function (): void {
        $output = new BufferedOutput();
        $consoleOutput = new ConsoleOutput($output);

        $consoleOutput->table(['Header1', 'Header2'], [['Value1', 'Value2']]);

        $display = $output->fetch();
        expect($display)->toContain('Header1')
            ->and($display)->toContain('Header2')
            ->and($display)->toContain('Value1')
            ->and($display)->toContain('Value2');
    });

    it('renders key value pair', function (): void {
        $output = new BufferedOutput();
        $consoleOutput = new ConsoleOutput($output);

        $consoleOutput->keyValue('Label', 'Value');

        $display = $output->fetch();
        expect($display)->toContain('Label')
            ->and($display)->toContain('Value');
    });

    it('renders key value with custom color', function (): void {
        $output = new BufferedOutput();
        $consoleOutput = new ConsoleOutput($output);

        $consoleOutput->keyValue('Status', 'OK', 'green-400');

        expect($output->fetch())->toContain('OK');
    });

    it('renders key value bool with true value', function (): void {
        $output = new BufferedOutput();
        $consoleOutput = new ConsoleOutput($output);

        $consoleOutput->keyValueBool('Active', true);

        $display = $output->fetch();
        expect($display)->toContain('Active')
            ->and($display)->toContain('Yes');
    });

    it('renders key value bool with false value', function (): void {
        $output = new BufferedOutput();
        $consoleOutput = new ConsoleOutput($output);

        $consoleOutput->keyValueBool('Active', false);

        $display = $output->fetch();
        expect($display)->toContain('Active')
            ->and($display)->toContain('No');
    });

    it('renders key value bool with custom colors', function (): void {
        $output = new BufferedOutput();
        $consoleOutput = new ConsoleOutput($output);

        $consoleOutput->keyValueBool('Status', true, 'blue-400', 'gray-400');

        expect($output->fetch())->toContain('Yes');
    });

    it('converts bool to string Yes', function (): void {
        $output = new BufferedOutput();
        $consoleOutput = new ConsoleOutput($output);

        expect($consoleOutput->boolToString(true))->toBe('Yes');
    });

    it('converts bool to string No', function (): void {
        $output = new BufferedOutput();
        $consoleOutput = new ConsoleOutput($output);

        expect($consoleOutput->boolToString(false))->toBe('No');
    });

    it('renders definition list', function (): void {
        $output = new BufferedOutput();
        $consoleOutput = new ConsoleOutput($output);

        $consoleOutput->definitionList([
            ['label' => 'Name', 'value' => 'Test'],
            ['label' => 'Status', 'value' => 'Active'],
        ]);

        $display = $output->fetch();
        expect($display)->toContain('Name')
            ->and($display)->toContain('Test')
            ->and($display)->toContain('Status')
            ->and($display)->toContain('Active');
    });

    it('renders new line', function (): void {
        $output = new BufferedOutput();
        $consoleOutput = new ConsoleOutput($output);

        $consoleOutput->newLine();

        expect($output->fetch())->not->toBeEmpty();
    });

    it('escapes html in table cells', function (): void {
        $output = new BufferedOutput();
        $consoleOutput = new ConsoleOutput($output);

        $consoleOutput->table(['Header'], [['<script>alert("xss")</script>']]);

        $display = $output->fetch();
        expect($display)->not->toContain('<script>');
    });

    it('renders table with multiple rows', function (): void {
        $output = new BufferedOutput();
        $consoleOutput = new ConsoleOutput($output);

        $consoleOutput->table(
            ['Name', 'Value'],
            [
                ['Row1', 'Value1'],
                ['Row2', 'Value2'],
                ['Row3', 'Value3'],
            ],
        );

        $display = $output->fetch();
        expect($display)->toContain('Row1')
            ->and($display)->toContain('Row2')
            ->and($display)->toContain('Row3');
    });

    it('renders empty table', function (): void {
        $output = new BufferedOutput();
        $consoleOutput = new ConsoleOutput($output);

        $consoleOutput->table(['Header'], []);

        expect($output->fetch())->toContain('Header');
    });
});
