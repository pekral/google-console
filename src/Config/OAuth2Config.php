<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\Config;

use JsonException;
use Pekral\GoogleConsole\Exception\GoogleConsoleFailure;

final readonly class OAuth2Config
{

    private const string DEFAULT_REDIRECT_URI = 'urn:ietf:wg:oauth:2.0:oob';

    public function __construct(
        public string $clientId,
        public string $clientSecret,
        public string $refreshToken,
        public ?string $redirectUri = null,
        public string $applicationName = 'Google Console Client',
    ) {
    }

    /**
     * Creates OAuth2Config from a path to OAuth2 client credentials JSON (e.g. client_secret_*.json)
     * and a refresh token obtained via the authorization code flow.
     *
     * @throws \Pekral\GoogleConsole\Exception\GoogleConsoleFailure When the credentials file is not found or invalid
     */
    public static function fromCredentialsPath(string $path, string $refreshToken): self
    {
        if (!file_exists($path)) {
            throw new GoogleConsoleFailure(sprintf('Credentials file not found: %s', $path));
        }

        $json = file_get_contents($path);

        if ($json === false) {
            throw new GoogleConsoleFailure(sprintf('Cannot read credentials file: %s', $path));
        }

        $config = self::decodeCredentialsJson($json);
        [$clientId, $clientSecret, $redirectUri] = self::extractOAuth2Block($config, $path);

        return new self(clientId: $clientId, clientSecret: $clientSecret, refreshToken: $refreshToken, redirectUri: $redirectUri);
    }

    public function getRedirectUri(): string
    {
        return $this->redirectUri ?? self::DEFAULT_REDIRECT_URI;
    }

    /**
     * @return array<string, mixed>
     * @throws \Pekral\GoogleConsole\Exception\GoogleConsoleFailure
     */
    private static function decodeCredentialsJson(string $json): array
    {
        try {
            $config = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new GoogleConsoleFailure('Invalid JSON in credentials file', 0, $e);
        }

        if (!is_array($config)) {
            throw new GoogleConsoleFailure('Credentials file must contain a JSON object');
        }

        // phpcs:ignore SlevomatCodingStandard.Commenting.InlineDocCommentDeclaration.MissingVariable -- PHPStan type for json_decode result
        /** @var array<string, mixed> $config */

        return $config;
    }

    /**
     * @param array<string, mixed> $config
     * @return array{0: string, 1: string, 2: string|null}
     * @throws \Pekral\GoogleConsole\Exception\GoogleConsoleFailure
     */
    // phpcs:ignore Generic.NamingConventions.CamelCapsFunctionName.ScopeNotCamelCaps -- OAuth2 is standard term
    private static function extractOAuth2Block(array $config, string $path): array
    {
        $key = isset($config['installed']) ? 'installed' : 'web';

        if (!isset($config[$key]) || !is_array($config[$key])) {
            throw new GoogleConsoleFailure(
                sprintf('OAuth2 credentials must contain "web" or "installed" key in %s', $path),
            );
        }

        $block = $config[$key];
        $clientId = $block['client_id'] ?? null;
        $clientSecret = $block['client_secret'] ?? null;
        $redirectUris = $block['redirect_uris'] ?? null;
        $redirectUriRaw = is_array($redirectUris) && isset($redirectUris[0]) ? $redirectUris[0] : null;
        $redirectUri = is_string($redirectUriRaw) ? $redirectUriRaw : null;

        if (!is_string($clientId) || !is_string($clientSecret)) {
            throw new GoogleConsoleFailure('OAuth2 credentials must contain client_id and client_secret');
        }

        return [$clientId, $clientSecret, $redirectUri];
    }

}
