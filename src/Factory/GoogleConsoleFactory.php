<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\Factory;

use Pekral\GoogleConsole\Config\BatchConfig;
use Pekral\GoogleConsole\Config\GoogleConfig;
use Pekral\GoogleConsole\Config\OAuth2Config;
use Pekral\GoogleConsole\GoogleConsole;
use Pekral\GoogleConsole\RateLimit\RateLimiterInterface;
use Pekral\GoogleConsole\UrlNormalizer\UrlNormalizer;

final class GoogleConsoleFactory
{

    public static function create(
        GoogleConfig $config,
        ?UrlNormalizer $urlNormalizer = null,
        ?BatchConfig $batchConfig = null,
        ?RateLimiterInterface $rateLimiter = null,
    ): GoogleConsole {
        $client = new GoogleClientFactory()->create($config);

        return new GoogleConsole($client, urlNormalizer: $urlNormalizer, batchConfig: $batchConfig, rateLimiter: $rateLimiter);
    }

    /**
     * Creates GoogleConsole from a service account credentials JSON path.
     *
     * @throws \Pekral\GoogleConsole\Exception\GoogleConsoleFailure When the credentials file is not found
     */
    public static function fromCredentialsPath(
        string $path,
        string $applicationName = 'Google Console Client',
        ?UrlNormalizer $urlNormalizer = null,
        ?BatchConfig $batchConfig = null,
        ?RateLimiterInterface $rateLimiter = null,
    ): GoogleConsole {
        $config = GoogleConfig::fromCredentialsPath($path);

        return self::create(
            new GoogleConfig(credentialsPath: $config->credentialsPath, applicationName: $applicationName),
            urlNormalizer: $urlNormalizer,
            batchConfig: $batchConfig,
            rateLimiter: $rateLimiter,
        );
    }

    /**
     * Creates GoogleConsole from OAuth2 credentials file and a refresh token.
     * Use when the end user has authorized your app via OAuth2 and you stored the refresh token.
     *
     * @param string $credentialsPath Path to OAuth2 client credentials JSON (e.g. client_secret_*.json from Google Cloud Console)
     * @param string $refreshToken Refresh token obtained from the authorization code flow
     * @param bool $fetchAccessToken When true (default), fetches access token immediately; set false only for testing without network
     * @throws \Pekral\GoogleConsole\Exception\GoogleConsoleFailure When the credentials file is not found or invalid
     */
    // phpcs:ignore Generic.NamingConventions.CamelCapsFunctionName.ScopeNotCamelCaps -- OAuth2 is standard term
    public static function fromOAuth2RefreshToken(
        string $credentialsPath,
        string $refreshToken,
        string $applicationName = 'Google Console Client',
        ?UrlNormalizer $urlNormalizer = null,
        ?BatchConfig $batchConfig = null,
        ?RateLimiterInterface $rateLimiter = null,
        bool $fetchAccessToken = true,
    ): GoogleConsole {
        $config = OAuth2Config::fromCredentialsPath($credentialsPath, $refreshToken);
        $config = new OAuth2Config(
            clientId: $config->clientId,
            clientSecret: $config->clientSecret,
            refreshToken: $config->refreshToken,
            redirectUri: $config->redirectUri,
            applicationName: $applicationName,
        );

        $client = new GoogleClientFactory()->createFromOAuth2($config, $fetchAccessToken);

        return new GoogleConsole($client, urlNormalizer: $urlNormalizer, batchConfig: $batchConfig, rateLimiter: $rateLimiter);
    }

    /**
     * Creates GoogleConsole from OAuth2Config (e.g. when credentials come from env or secrets manager).
     *
     * @param bool $fetchAccessToken When true (default), fetches access token immediately; set false only for testing without network
     * @throws \Pekral\GoogleConsole\Exception\GoogleConsoleFailure When token refresh fails
     */
    // phpcs:ignore Generic.NamingConventions.CamelCapsFunctionName.ScopeNotCamelCaps -- OAuth2 is standard term
    public static function fromOAuth2Config(
        OAuth2Config $config,
        ?UrlNormalizer $urlNormalizer = null,
        ?BatchConfig $batchConfig = null,
        ?RateLimiterInterface $rateLimiter = null,
        bool $fetchAccessToken = true,
    ): GoogleConsole {
        $client = new GoogleClientFactory()->createFromOAuth2($config, $fetchAccessToken);

        return new GoogleConsole($client, urlNormalizer: $urlNormalizer, batchConfig: $batchConfig, rateLimiter: $rateLimiter);
    }

}
