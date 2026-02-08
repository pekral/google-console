<?php

declare(strict_types = 1);

require __DIR__ . '/../vendor/autoload.php';

use Pekral\GoogleConsole\Command\GetSiteCommand;
use Pekral\GoogleConsole\Command\InspectUrlCommand;
use Pekral\GoogleConsole\Command\ListSitesCommand;
use Pekral\GoogleConsole\Command\RequestIndexingCommand;
use Pekral\GoogleConsole\Command\SearchAnalyticsCommand;
use Symfony\Component\Console\Application;

$credentials = getenv('GOOGLE_CREDENTIALS_PATH') ?: __DIR__ . '/../laserstorm-2d1c761d5a97.json';

if ($credentials === '' || !is_file($credentials)) {
    echo "Error: Credentials file not found.\n";
    echo "Set the GOOGLE_CREDENTIALS_PATH environment variable.\n";
    exit(1);
}

$application = new Application('Pekral Google Console', '1.0.0');
$application->add(new ListSitesCommand());
$application->add(new GetSiteCommand());
$application->add(new SearchAnalyticsCommand());
$application->add(new InspectUrlCommand());
$application->add(new RequestIndexingCommand());
$application->setAutoExit(false);
