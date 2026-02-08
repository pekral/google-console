<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\Command;

use Pekral\GoogleConsole\Command\Output\ConsoleOutput;
use Pekral\GoogleConsole\DTO\IndexingCheckResult;
use Pekral\GoogleConsole\DTO\UrlInspectionResult;
use Pekral\GoogleConsole\Enum\IndexingCheckReasonCode;
use Pekral\GoogleConsole\Enum\OperatingMode;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Inspects a URL to check its indexing status and mobile usability in Google Search.
 */
#[AsCommand(name: 'pekral:google-url-inspect', description: 'Inspects a URL to check its indexing status and mobile usability')]
final class InspectUrlCommand extends BaseGoogleConsoleCommand
{

    protected function configure(): void
    {
        parent::configure();

        $this->addArgument('site-url', InputArgument::REQUIRED, 'The site URL that owns the inspected page (e.g., https://example.com/)');
        $this->addArgument('inspection-url', InputArgument::REQUIRED, 'The full URL to inspect (e.g., https://example.com/page)');
        $this->addOption(
            'mode',
            'm',
            InputOption::VALUE_REQUIRED,
            'Operating mode: strict (never INDEXED high without authoritative data) or best-effort (allow heuristic)',
            OperatingMode::STRICT->value,
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $siteUrl = $input->getArgument('site-url');
        assert(is_string($siteUrl));

        $inspectionUrl = $input->getArgument('inspection-url');
        assert(is_string($inspectionUrl));

        $operatingMode = $this->resolveOperatingMode($input);
        $result = $this->getGoogleConsole($input)->inspectUrl($siteUrl, $inspectionUrl, $operatingMode);

        if ($this->isJsonOutput($input)) {
            $this->outputJson($output, ['siteUrl' => $siteUrl, 'inspectionUrl' => $inspectionUrl, ...$result->toArray()]);

            return self::SUCCESS;
        }

        $out = $this->createOutput($output);
        $out->header('Google Search Console - URL Inspection');

        $this->displayIndexingStatus($out, $result);

        if ($result->indexingCheckResult !== null) {
            $this->displayIndexingCheckResult($out, $result->indexingCheckResult);
        }

        $this->displayCanonicalUrls($out, $result);
        $this->displayMobileUsability($out, $result);

        return self::SUCCESS;
    }

    private function displayIndexingCheckResult(ConsoleOutput $out, IndexingCheckResult $check): void
    {
        $statusColor = match ($check->primaryStatus->value) {
            'INDEXED' => 'green-400',
            'NOT_INDEXED' => 'red-400',
            'UNKNOWN' => 'yellow-400',
        };
        $reasonCodesList = implode(', ', array_map(static fn (IndexingCheckReasonCode $code): string => $code->value, $check->reasonCodes));

        $out->section('Business output (indexing check)');
        $out->keyValue('Primary status', $check->primaryStatus->value, $statusColor);
        $out->keyValue('Confidence', $check->confidence->value);
        $out->keyValue('Reason codes', $reasonCodesList !== '' ? $reasonCodesList : 'none');
        $out->keyValue('Checked at', $check->checkedAt->format('Y-m-d H:i:s'));
        $out->keyValue('Source type', $check->sourceType->value);
    }

    private function displayIndexingStatus(ConsoleOutput $out, UrlInspectionResult $result): void
    {
        $out->section('Indexing Status');
        $out->keyValue('Verdict', $result->verdict, $result->isIndexed() ? 'green-400' : 'red-400');
        $out->keyValueBool('Is Indexed', $result->isIndexed());
        $out->keyValueBool('Is Indexable', $result->isIndexable(), 'green-400', 'yellow-400');
        $out->keyValueBool('Is Crawlable', $result->isCrawlable(), 'green-400', 'yellow-400');
        $out->keyValue('Coverage State', $result->coverageState);
        $out->keyValue('Indexing State', $result->indexingState);
        $out->keyValue('Robots.txt', $result->robotsTxtState);
        $out->keyValue('Page Fetch', $result->pageFetchState);
        $out->keyValue('Crawled As', $result->crawledAs ?? 'N/A');
        $out->keyValue('Last Crawl', $result->lastCrawlTime?->format('Y-m-d H:i:s') ?? 'N/A');
    }

    private function displayCanonicalUrls(ConsoleOutput $out, UrlInspectionResult $result): void
    {
        $out->section('Canonical URLs');
        $out->keyValue('Google', $result->googleCanonical ?? 'N/A');
        $out->keyValue('User', $result->userCanonical ?? 'N/A');
    }

    private function displayMobileUsability(ConsoleOutput $out, UrlInspectionResult $result): void
    {
        $out->section('Mobile Usability');
        $out->keyValueBool('Mobile Friendly', $result->isMobileFriendly);
        $out->keyValue('Issues', $result->mobileUsabilityIssue ?? 'None', $result->mobileUsabilityIssue !== null ? 'yellow-400' : 'green-400');

        if ($result->inspectionResultLink !== '') {
            $out->newLine();
            $out->info(sprintf('Full report: %s', $result->inspectionResultLink));
        }

        $out->newLine();
    }

    private function resolveOperatingMode(InputInterface $input): ?OperatingMode
    {
        $value = $input->getOption('mode');

        if (!is_string($value) || $value === '') {
            return null;
        }

        return OperatingMode::tryFrom($value) ?? null;
    }

}
