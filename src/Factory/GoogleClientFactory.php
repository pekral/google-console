<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\Factory;

use Google\Client;
use Google\Service\SearchConsole as SearchConsoleService;
use Google\Service\Webmasters as WebmastersService;
use Pekral\GoogleConsole\Config\GoogleConfig;
use Pekral\GoogleConsole\Config\OAuth2Config;

final class GoogleClientFactory
{

    public const string INDEXING_SCOPE = 'https://www.googleapis.com/auth/indexing';

    public function create(GoogleConfig $config): Client
    {
        $client = new Client();
        $client->setApplicationName($config->applicationName);
        $client->addScope(WebmastersService::WEBMASTERS);
        $client->addScope(WebmastersService::WEBMASTERS_READONLY);
        $client->addScope(SearchConsoleService::WEBMASTERS_READONLY);
        $client->addScope(self::INDEXING_SCOPE);
        $client->setAuthConfig($config->credentialsPath);

        return $client;
    }

    // phpcs:ignore Generic.NamingConventions.CamelCapsFunctionName.ScopeNotCamelCaps -- OAuth2 is standard term
    public function createFromOAuth2(OAuth2Config $config, bool $fetchAccessToken = true): Client
    {
        $client = new Client();
        $client->setApplicationName($config->applicationName);
        $client->addScope(WebmastersService::WEBMASTERS);
        $client->addScope(WebmastersService::WEBMASTERS_READONLY);
        $client->addScope(SearchConsoleService::WEBMASTERS_READONLY);
        $client->addScope(self::INDEXING_SCOPE);
        $client->setAuthConfig([
            'client_id' => $config->clientId,
            'client_secret' => $config->clientSecret,
            'redirect_uris' => [$config->getRedirectUri()],
        ]);
        $client->setAccessToken([
            'access_token' => 'placeholder',
            'refresh_token' => $config->refreshToken,
        ]);

        if ($fetchAccessToken) {
            $client->fetchAccessTokenWithRefreshToken($config->refreshToken);
        }

        return $client;
    }

}
