<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Retrieves and displays information about a specific site from Google Search Console.
 */
#[AsCommand(name: 'pekral:google-site-get', description: 'Retrieves information about a specific site from Google Search Console')]
final class GetSiteCommand extends BaseGoogleConsoleCommand
{

    protected function configure(): void
    {
        parent::configure();

        $this->addArgument('site-url', InputArgument::REQUIRED, 'The site URL (e.g., https://example.com/ or sc-domain:example.com)');
    }

    /**
     * @throws \Pekral\GoogleConsole\Exception\GoogleConsoleFailure
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $siteUrl = $input->getArgument('site-url');
        assert(is_string($siteUrl));

        $site = $this->getGoogleConsole($input)->getSite($siteUrl);

        if ($this->isJsonOutput($input)) {
            $this->outputJson($output, $site->toArray());

            return self::SUCCESS;
        }

        $out = $this->createOutput($output);
        $out->header('Google Search Console - Site Details');

        $out->section('Site Information');
        $out->keyValue('Site URL', $site->siteUrl);
        $out->keyValue('Permission', $site->permissionLevel);
        $out->keyValueBool('Is Owner', $site->isOwner(), 'green-400', 'gray-400');
        $out->keyValueBool('Full Access', $site->hasFullAccess(), 'green-400', 'gray-400');
        $out->newLine();

        return self::SUCCESS;
    }

}
