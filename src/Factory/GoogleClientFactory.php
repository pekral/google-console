<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\Factory;

use Google\Client;
use Google\Service\SearchConsole as SearchConsoleService;
use Google\Service\Webmasters as WebmastersService;
use Pekral\GoogleConsole\Config\GoogleConfig;

final class GoogleClientFactory
{

    public const string INDEXING_SCOPE = 'https://www.googleapis.com/auth/indexing';

    public function create(GoogleConfig $config): Client
    {
        $client = new Client();
        $client->setApplicationName($config->applicationName);
        $client->addScope(WebmastersService::WEBMASTERS_READONLY);
        $client->addScope(SearchConsoleService::WEBMASTERS_READONLY);
        $client->addScope(self::INDEXING_SCOPE);
        $client->setAuthConfig($config->credentialsPath);

        return $client;
    }

}
