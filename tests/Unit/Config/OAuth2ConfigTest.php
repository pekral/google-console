<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\Config\OAuth2Config;
use Pekral\GoogleConsole\Exception\GoogleConsoleFailure;

describe(OAuth2Config::class, function (): void {

    it('creates config from credentials path with web key', function (): void {
        $tempFile = sys_get_temp_dir() . '/test-oauth2-' . uniqid() . '.json';
        file_put_contents($tempFile, json_encode([
            'web' => [
                'client_id' => 'cid.apps.googleusercontent.com',
                'client_secret' => 'secret',
                'redirect_uris' => ['https://example.com/callback'],
            ],
        ]));

        $config = OAuth2Config::fromCredentialsPath($tempFile, 'refresh-token-123');

        expect($config->clientId)->toBe('cid.apps.googleusercontent.com')
            ->and($config->clientSecret)->toBe('secret')
            ->and($config->refreshToken)->toBe('refresh-token-123')
            ->and($config->redirectUri)->toBe('https://example.com/callback')
            ->and($config->getRedirectUri())->toBe('https://example.com/callback');

        unlink($tempFile);
    });

    it('creates config from credentials path with installed key', function (): void {
        $tempFile = sys_get_temp_dir() . '/test-oauth2-' . uniqid() . '.json';
        file_put_contents($tempFile, json_encode([
            'installed' => [
                'client_id' => 'cid.apps.googleusercontent.com',
                'client_secret' => 'secret',
                'redirect_uris' => ['urn:ietf:wg:oauth:2.0:oob'],
            ],
        ]));

        $config = OAuth2Config::fromCredentialsPath($tempFile, 'rt456');

        expect($config->clientId)->toBe('cid.apps.googleusercontent.com')
            ->and($config->redirectUri)->toBe('urn:ietf:wg:oauth:2.0:oob')
            ->and($config->getRedirectUri())->toBe('urn:ietf:wg:oauth:2.0:oob');

        unlink($tempFile);
    });

    it('uses default redirect URI when not in credentials', function (): void {
        $tempFile = sys_get_temp_dir() . '/test-oauth2-' . uniqid() . '.json';
        file_put_contents($tempFile, json_encode([
            'web' => [
                'client_id' => 'cid',
                'client_secret' => 'sec',
            ],
        ]));

        $config = OAuth2Config::fromCredentialsPath($tempFile, 'rt');

        expect($config->redirectUri)->toBeNull()
            ->and($config->getRedirectUri())->toBe('urn:ietf:wg:oauth:2.0:oob');

        unlink($tempFile);
    });

    it('throws exception when credentials file not found', function (): void {
        OAuth2Config::fromCredentialsPath('/non/existent/oauth.json', 'rt');
    })->throws(GoogleConsoleFailure::class, 'Credentials file not found: /non/existent/oauth.json');

    it('throws exception when credentials file cannot be read', function (): void {
        $tempFile = sys_get_temp_dir() . '/test-oauth2-' . uniqid() . '.json';
        touch($tempFile);
        chmod($tempFile, 0o000);

        try {
            OAuth2Config::fromCredentialsPath($tempFile, 'rt');
        } finally {
            chmod($tempFile, 0o644);
            unlink($tempFile);
        }
    })->throws(GoogleConsoleFailure::class, 'Cannot read credentials file');

    it('throws exception when JSON is invalid', function (): void {
        $tempFile = sys_get_temp_dir() . '/test-oauth2-' . uniqid() . '.json';
        file_put_contents($tempFile, 'not json');

        OAuth2Config::fromCredentialsPath($tempFile, 'rt');
    })->throws(GoogleConsoleFailure::class, 'Invalid JSON in credentials file');

    it('throws exception when JSON is not an object', function (): void {
        $tempFile = sys_get_temp_dir() . '/test-oauth2-' . uniqid() . '.json';
        file_put_contents($tempFile, 'null');

        try {
            OAuth2Config::fromCredentialsPath($tempFile, 'rt');
        } finally {
            unlink($tempFile);
        }
    })->throws(GoogleConsoleFailure::class, 'Credentials file must contain a JSON object');

    it('throws exception when web or installed key is missing', function (): void {
        $tempFile = sys_get_temp_dir() . '/test-oauth2-' . uniqid() . '.json';
        file_put_contents($tempFile, json_encode(['client_id' => 'x']));

        OAuth2Config::fromCredentialsPath($tempFile, 'rt');
    })->throws(GoogleConsoleFailure::class, 'OAuth2 credentials must contain "web" or "installed" key');

    it('throws exception when client_id or client_secret is missing', function (): void {
        $tempFile = sys_get_temp_dir() . '/test-oauth2-' . uniqid() . '.json';
        file_put_contents($tempFile, json_encode([
            'web' => ['redirect_uris' => []],
        ]));

        OAuth2Config::fromCredentialsPath($tempFile, 'rt');
    })->throws(GoogleConsoleFailure::class, 'OAuth2 credentials must contain client_id and client_secret');

    it('creates config from constructor with default redirect URI', function (): void {
        $config = new OAuth2Config(
            clientId: 'cid',
            clientSecret: 'sec',
            refreshToken: 'rt',
        );

        expect($config->getRedirectUri())->toBe('urn:ietf:wg:oauth:2.0:oob')
            ->and($config->applicationName)->toBe('Google Console Client');
    });

    it('creates config from constructor with custom application name', function (): void {
        $config = new OAuth2Config(
            clientId: 'cid',
            clientSecret: 'sec',
            refreshToken: 'rt',
            applicationName: 'My App',
        );

        expect($config->applicationName)->toBe('My App');
    });
});
