<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\Command;

use Pekral\GoogleConsole\DTO\Site;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Lists all sites registered in Google Search Console for the authenticated account.
 */
#[AsCommand(name: 'pekral:google-sites-list', description: 'Lists all sites registered in Google Search Console')]
final class ListSitesCommand extends BaseGoogleConsoleCommand
{

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sites = $this->getGoogleConsole($input)->getSiteList();

        if ($this->isJsonOutput($input)) {
            $this->outputJson($output, [
                'count' => count($sites),
                'sites' => array_map(static fn (Site $site): array => $site->toArray(), $sites),
            ]);

            return self::SUCCESS;
        }

        $out = $this->createOutput($output);
        $out->header('Google Search Console - Sites');

        if ($sites === []) {
            $out->warning('No sites found.');

            return self::SUCCESS;
        }

        $out->section('Registered Sites');

        $out->table(
            ['Site URL', 'Permission', 'Owner', 'Full Access'],
            array_map(
                static fn (Site $site): array => [
                    $site->siteUrl,
                    $site->permissionLevel,
                    $site->isOwner() ? 'Yes' : 'No',
                    $site->hasFullAccess() ? 'Yes' : 'No',
                ],
                $sites,
            ),
        );

        $out->success(sprintf('Found %d site(s).', count($sites)));

        return self::SUCCESS;
    }

}
