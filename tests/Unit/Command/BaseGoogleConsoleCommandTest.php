<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\Command\BaseGoogleConsoleCommand;
use Pekral\GoogleConsole\Command\Output\ConsoleOutput;
use Pekral\GoogleConsole\ConsoleContract;
use Pekral\GoogleConsole\Exception\GoogleConsoleFailure;
use Pekral\GoogleConsole\GoogleConsole;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

describe(BaseGoogleConsoleCommand::class, function (): void {

    it('has credentials option', function (): void {
        $command = new class () extends BaseGoogleConsoleCommand {

            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                $input->getOption('credentials');
                $output->writeln('');

                return Command::SUCCESS;
            }

        };

        $definition = $command->getDefinition();

        expect($definition->hasOption('credentials'))->toBeTrue()
            ->and($definition->getOption('credentials')->getShortcut())->toBe('c')
            ->and($definition->getOption('credentials')->isValueRequired())->toBeTrue();
    });

    it('creates google pekral-google from credentials path', function (): void {
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

        $command = new class () extends BaseGoogleConsoleCommand {

            private ?ConsoleContract $consoleContract = null;

            public function getCreatedConsole(): ?ConsoleContract
            {
                return $this->consoleContract;
            }

            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                $this->consoleContract = $this->getGoogleConsole($input);
                $output->writeln('');

                return Command::SUCCESS;
            }

        };

        $tester = new CommandTester($command);
        $tester->execute(['--credentials' => $tempFile]);

        expect($command->getCreatedConsole())->toBeInstanceOf(GoogleConsole::class);

        unlink($tempFile);
    });

    it('uses injected google pekral-google when set', function (): void {
        $googleConsole = Mockery::mock(ConsoleContract::class);

        $command = new class () extends BaseGoogleConsoleCommand {

            private ?ConsoleContract $consoleContract = null;

            public function getUsedConsole(): ?ConsoleContract
            {
                return $this->consoleContract;
            }

            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                $this->consoleContract = $this->getGoogleConsole($input);
                $output->writeln('');

                return Command::SUCCESS;
            }

        };

        $command->setGoogleConsole($googleConsole);

        $tester = new CommandTester($command);
        $tester->execute([]);

        expect($command->getUsedConsole())->toBe($googleConsole);
    });

    it('creates pekral-google output instance', function (): void {
        $command = new class () extends BaseGoogleConsoleCommand {

            private ?ConsoleOutput $consoleOutput = null;

            public function getCreatedOutput(): ?ConsoleOutput
            {
                return $this->consoleOutput;
            }

            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                $input->getOption('credentials');
                $this->consoleOutput = $this->createOutput($output);

                return Command::SUCCESS;
            }

        };

        $tester = new CommandTester($command);
        $tester->execute([]);

        expect($command->getCreatedOutput())->toBeInstanceOf(ConsoleOutput::class);
    });

    it('throws exception when credentials path is missing', function (): void {
        $command = new class () extends BaseGoogleConsoleCommand {

            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                $output->writeln('');
                $this->getGoogleConsole($input);

                return Command::SUCCESS;
            }

        };

        $tester = new CommandTester($command);

        expect(fn (): int => $tester->execute([]))->toThrow(
            GoogleConsoleFailure::class,
            'Credentials path is required.',
        );
    });

    it('uses credentials from environment variable', function (): void {
        $tempFile = sys_get_temp_dir() . '/test-credentials-env-' . uniqid() . '.json';
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

        putenv('GOOGLE_CREDENTIALS_PATH=' . $tempFile);

        $command = new class () extends BaseGoogleConsoleCommand {

            private ?ConsoleContract $consoleContract = null;

            public function getCreatedConsole(): ?ConsoleContract
            {
                return $this->consoleContract;
            }

            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                $this->consoleContract = $this->getGoogleConsole($input);
                $output->writeln('');

                return Command::SUCCESS;
            }

        };

        $tester = new CommandTester($command);
        $tester->execute([]);

        expect($command->getCreatedConsole())->toBeInstanceOf(GoogleConsole::class);

        putenv('GOOGLE_CREDENTIALS_PATH');
        unlink($tempFile);
    });

    it('prefers credentials option over environment variable', function (): void {
        $tempFileOption = sys_get_temp_dir() . '/test-credentials-option-' . uniqid() . '.json';
        $tempFileEnv = sys_get_temp_dir() . '/test-credentials-env-' . uniqid() . '.json';

        $credentials = [
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
        ];

        file_put_contents($tempFileOption, json_encode($credentials));
        file_put_contents($tempFileEnv, json_encode($credentials));

        putenv('GOOGLE_CREDENTIALS_PATH=' . $tempFileEnv);

        $command = new class () extends BaseGoogleConsoleCommand {

            private ?string $usedPath = null;

            public function getUsedPath(): ?string
            {
                return $this->usedPath;
            }

            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                $output->writeln('');
                $this->getGoogleConsole($input);
                $credentialsOption = $input->getOption('credentials');
                $this->usedPath = is_string($credentialsOption) ? $credentialsOption : null;

                return Command::SUCCESS;
            }

        };

        $googleConsole = Mockery::mock(ConsoleContract::class);
        $command->setGoogleConsole($googleConsole);

        $tester = new CommandTester($command);
        $tester->execute(['--credentials' => $tempFileOption]);

        expect($command->getUsedPath())->toBe($tempFileOption);

        putenv('GOOGLE_CREDENTIALS_PATH');
        unlink($tempFileOption);
        unlink($tempFileEnv);
    });

    it('has json option', function (): void {
        $command = new class () extends BaseGoogleConsoleCommand {

            /**
             * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
             */
            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                $input->getOption('json');

                return Command::SUCCESS;
            }

        };

        $definition = $command->getDefinition();

        expect($definition->hasOption('json'))->toBeTrue()
            ->and($definition->getOption('json')->getShortcut())->toBe('j')
            ->and($definition->getOption('json')->isValueRequired())->toBeFalse();
    });

    it('detects json output mode', function (): void {
        $command = new class () extends BaseGoogleConsoleCommand {

            private bool $isJson = false;

            public function isJson(): bool
            {
                return $this->isJson;
            }

            /**
             * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
             */
            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                $this->isJson = $this->isJsonOutput($input);

                return Command::SUCCESS;
            }

        };

        $tester = new CommandTester($command);
        $tester->execute(['--json' => true]);

        expect($command->isJson())->toBeTrue();
    });

    it('outputs json data correctly', function (): void {
        $command = new class () extends BaseGoogleConsoleCommand {

            /**
             * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
             */
            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                $this->outputJson($output, ['test' => 'value', 'number' => 42]);

                return Command::SUCCESS;
            }

        };

        $tester = new CommandTester($command);
        $tester->execute([]);

        $decoded = json_decode($tester->getDisplay(), true, 512, JSON_THROW_ON_ERROR);

        expect($decoded)->toBe(['test' => 'value', 'number' => 42]);
    });
});
