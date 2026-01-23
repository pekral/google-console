<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\Command;

use Pekral\GoogleConsole\Command\Output\ConsoleOutput;
use Pekral\GoogleConsole\ConsoleContract;
use Pekral\GoogleConsole\Exception\GoogleConsoleFailure;
use Pekral\GoogleConsole\GoogleConsole;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Base command providing shared credentials handling for Google Console commands.
 */
abstract class BaseGoogleConsoleCommand extends Command
{

    private const string ENV_CREDENTIALS = 'GOOGLE_CREDENTIALS_PATH';

    private ?ConsoleContract $consoleContract = null;

    public function setGoogleConsole(ConsoleContract $googleConsole): void
    {
        $this->consoleContract = $googleConsole;
    }

    protected function configure(): void
    {
        $this->addOption(
            'credentials',
            'c',
            InputOption::VALUE_REQUIRED,
            sprintf('Path to the Google service account credentials JSON file (or set %s env variable)', self::ENV_CREDENTIALS),
        );

        $this->addOption('json', 'j', InputOption::VALUE_NONE, 'Output results in JSON format');
    }

    protected function getGoogleConsole(InputInterface $input): ConsoleContract
    {
        if ($this->consoleContract !== null) {
            return $this->consoleContract;
        }

        $credentialsPath = $this->resolveCredentialsPath($input);

        return GoogleConsole::fromCredentialsPath($credentialsPath);
    }

    protected function createOutput(OutputInterface $output): ConsoleOutput
    {
        return new ConsoleOutput($output);
    }

    protected function isJsonOutput(InputInterface $input): bool
    {
        return $input->getOption('json') === true;
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function outputJson(OutputInterface $output, array $data): void
    {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        // @codeCoverageIgnoreStart
        if ($json === false) {
            throw new GoogleConsoleFailure('Failed to encode data to JSON');
        }

        // @codeCoverageIgnoreEnd

        $output->writeln($json);
    }

    private function resolveCredentialsPath(InputInterface $input): string
    {
        $credentialsPath = $input->getOption('credentials');

        if (is_string($credentialsPath) && $credentialsPath !== '') {
            return $credentialsPath;
        }

        $envPath = getenv(self::ENV_CREDENTIALS);

        if (is_string($envPath) && $envPath !== '') {
            return $envPath;
        }

        throw new GoogleConsoleFailure(sprintf(
            'Credentials path is required. Use --credentials (-c) option or set %s environment variable.',
            self::ENV_CREDENTIALS,
        ));
    }

}
