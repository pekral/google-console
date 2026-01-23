<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\Command;

use DateTimeImmutable;
use Pekral\GoogleConsole\Command\Output\ConsoleOutput;
use Pekral\GoogleConsole\DTO\SearchAnalyticsRow;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Retrieves search analytics data for a site within a specified date range.
 */
#[AsCommand(name: 'pekral:google-analytics-search', description: 'Retrieves search analytics data for a site from Google Search Console')]
final class SearchAnalyticsCommand extends BaseGoogleConsoleCommand
{

    protected function configure(): void
    {
        parent::configure();

        $this->addArgument('site-url', InputArgument::REQUIRED, 'The site URL (e.g., https://example.com/ or sc-domain:example.com)');
        $this->addOption(
            'start-date',
            's',
            InputOption::VALUE_REQUIRED,
            'Start date for analytics (Y-m-d format)',
            new DateTimeImmutable('-30 days')->format('Y-m-d'),
        );
        $this->addOption('end-date', 'e', InputOption::VALUE_REQUIRED, 'End date for analytics (Y-m-d format)', new DateTimeImmutable()->format('Y-m-d'));
        $this->addOption(
            'dimensions',
            'd',
            InputOption::VALUE_REQUIRED,
            'Comma-separated dimensions: query, page, country, device, searchAppearance, date',
            'query',
        );
        $this->addOption('row-limit', 'l', InputOption::VALUE_REQUIRED, 'Maximum number of rows to return', '100');
        $this->addOption('start-row', 'r', InputOption::VALUE_REQUIRED, 'Starting row offset for pagination', '0');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $params = $this->extractParameters($input);
        $rows = $this->getGoogleConsole($input)->getSearchAnalytics(...$params);

        if ($this->isJsonOutput($input)) {
            $this->outputJson($output, [
                'count' => count($rows),
                'dimensions' => $params['dimensions'],
                'endDate' => $params['endDate']->format('Y-m-d'),
                'rows' => array_map(static fn (SearchAnalyticsRow $row): array => $row->toArray(), $rows),
                'siteUrl' => $params['siteUrl'],
                'startDate' => $params['startDate']->format('Y-m-d'),
            ]);

            return self::SUCCESS;
        }

        $out = $this->createOutput($output);
        $out->header('Google Search Console - Analytics');

        if ($rows === []) {
            $out->warning('No search analytics data found for the specified period.');

            return self::SUCCESS;
        }

        $this->displayResults($out, $rows, $params['dimensions'], $params['startDate'], $params['endDate']);

        return self::SUCCESS;
    }

    /**
     * @return array{
     *     siteUrl: string,
     *     startDate: \DateTimeImmutable,
     *     endDate: \DateTimeImmutable,
     *     dimensions: array<string>,
     *     rowLimit: int,
     *     startRow: int
     * }
     */
    private function extractParameters(InputInterface $input): array
    {
        $siteUrl = $input->getArgument('site-url');
        assert(is_string($siteUrl));

        $startDateOption = $input->getOption('start-date');
        assert(is_string($startDateOption));

        $endDateOption = $input->getOption('end-date');
        assert(is_string($endDateOption));

        $dimensionsOption = $input->getOption('dimensions');
        assert(is_string($dimensionsOption));

        $rowLimitOption = $input->getOption('row-limit');
        assert(is_string($rowLimitOption));

        $startRowOption = $input->getOption('start-row');
        assert(is_string($startRowOption));

        return [
            'dimensions' => array_map('trim', explode(',', $dimensionsOption)),
            'endDate' => new DateTimeImmutable($endDateOption),
            'rowLimit' => (int) $rowLimitOption,
            'siteUrl' => $siteUrl,
            'startDate' => new DateTimeImmutable($startDateOption),
            'startRow' => (int) $startRowOption,
        ];
    }

    /**
     * @param array<\Pekral\GoogleConsole\DTO\SearchAnalyticsRow> $rows
     * @param array<string> $dimensions
     */
    private function displayResults(ConsoleOutput $out, array $rows, array $dimensions, DateTimeImmutable $startDate, DateTimeImmutable $endDate): void
    {
        $out->section(sprintf('Search Performance (%s - %s)', $startDate->format('Y-m-d'), $endDate->format('Y-m-d')));

        $headers = [...$dimensions, 'Clicks', 'Impressions', 'CTR', 'Position'];

        $tableRows = array_map(
            static fn (SearchAnalyticsRow $row): array => [
                ...array_map(static fn (string $dim): string => $row->getKey($dim) ?? '', $dimensions),
                number_format($row->clicks, 0),
                number_format($row->impressions, 0),
                number_format($row->ctr * 100, 2) . '%',
                number_format($row->position, 1),
            ],
            $rows,
        );

        $out->table($headers, $tableRows);
        $out->success(sprintf('Found %d row(s).', count($rows)));
    }

}
