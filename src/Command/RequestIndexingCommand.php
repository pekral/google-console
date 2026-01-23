<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\Command;

use Pekral\GoogleConsole\Enum\IndexingNotificationType;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Requests Google to index or remove a URL via the Indexing API.
 */
#[AsCommand(name: 'pekral:google-request-indexing', description: 'Requests Google to index or remove a URL')]
final class RequestIndexingCommand extends BaseGoogleConsoleCommand
{

    protected function configure(): void
    {
        parent::configure();

        $this->addArgument('url', InputArgument::REQUIRED, 'The full URL to request indexing for (e.g., https://example.com/page)');
        $this->addOption('delete', 'd', InputOption::VALUE_NONE, 'Request URL removal instead of indexing');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $url = $input->getArgument('url');
        assert(is_string($url));

        $isDelete = $input->getOption('delete') === true;
        $type = $isDelete ? IndexingNotificationType::URL_DELETED : IndexingNotificationType::URL_UPDATED;

        $result = $this->getGoogleConsole($input)->requestIndexing($url, $type);

        if ($this->isJsonOutput($input)) {
            $this->outputJson($output, $result->toArray());

            return self::SUCCESS;
        }

        $out = $this->createOutput($output);
        $out->header('Google Indexing API - Request Indexing');

        $out->section('Request Details');
        $out->keyValue('URL', $result->url);
        $out->keyValue('Type', $result->type->value, $isDelete ? 'red-400' : 'green-400');
        $out->keyValue('Notify Time', $result->notifyTime?->format('Y-m-d H:i:s') ?? 'N/A');

        $out->newLine();

        if ($isDelete) {
            $out->success('URL removal request submitted successfully.');
        } else {
            $out->success('Indexing request submitted successfully.');
        }

        $out->newLine();

        return self::SUCCESS;
    }

}
