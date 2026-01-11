<?php

declare(strict_types = 1);

namespace Pekral\PhpcsRulesBuild\Command;

use FilesystemIterator;
use Pekral\PhpcsRulesBuild\Sniffs\SniffHelper;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

use function array_diff;
use function assert;
use function implode;
use function is_dir;
use function is_file;

#[AsCommand(name: 'build:check-unused-sniffs', description: 'Find unused new sniffs', help: 'Find unused new sniffs')]
final class UnusedSniffCommand extends Command
{

    private const string BASE_PATH = __DIR__ . '/../../';
    private const string SNIFFS_DIR = self::BASE_PATH . '/vendor/slevomat/coding-standard/SlevomatCodingStandard/Sniffs/';
    private const string RULESET_FILE = self::BASE_PATH . 'ruleset.xml';

    /**
     * @param array<string> $realUnused
     */
    private function printUnusedSniffs(array $realUnused, SymfonyStyle $output): int
    {
        if (count($realUnused) === 0) {
            $output->info('All Slevomat sniffs are used in ruleset.xml');

            return Command::SUCCESS;
        }

        $output->error('Unused Slevomat sniffs in ruleset.xml');
        $output->writeln(implode("\n", $realUnused));

        return Command::FAILURE;
    }

    /**
     * @param array<string> $allSniffs
     * @return array<string>
     */
    private function getUnusedSniffs(array $allSniffs): array
    {
        return array_diff($allSniffs, SniffHelper::getAllSniffsFromRuleset(self::RULESET_FILE));
    }

    /**
     * @return \RecursiveIteratorIterator<\RecursiveDirectoryIterator>
     */
    private function getSniffsIterator(): RecursiveIteratorIterator
    {
        return new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(self::SNIFFS_DIR, FilesystemIterator::SKIP_DOTS),
        );
    }

    /**
     * @return array<string>
     */
    private function getAllAvailableSniffs(): array
    {
        $allSniffs = [];

        foreach ($this->getSniffsIterator() as $item) {
            assert($item instanceof SplFileInfo);

            if (!$item->isDir()) {
                $allSniffs[] = SniffHelper::getSniffName($item);
            }
        }

        return $allSniffs;
    }

    public function __invoke(SymfonyStyle $output): int
    {
        if (!is_dir(self::SNIFFS_DIR)) {
            $output->writeln('Sniffs directory not found: ' . self::SNIFFS_DIR);

            return Command::FAILURE;
        }

        if (!is_file(self::RULESET_FILE)) {
            $output->writeln('Ruleset file not found: ' . self::SNIFFS_DIR);

            return Command::FAILURE;
        }

        return $this->printUnusedSniffs($this->getUnusedSniffs($this->getAllAvailableSniffs()), $output);
    }

}