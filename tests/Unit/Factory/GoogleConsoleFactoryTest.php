<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\Config\GoogleConfig;
use Pekral\GoogleConsole\Config\OAuth2Config;
use Pekral\GoogleConsole\Exception\GoogleConsoleFailure;
use Pekral\GoogleConsole\Factory\GoogleConsoleFactory;
use Pekral\GoogleConsole\GoogleConsole;

describe(GoogleConsoleFactory::class, function (): void {

    it('creates GoogleConsole from config', function (): void {
        $tempFile = sys_get_temp_dir() . '/test-credentials-' . uniqid() . '.json';
        file_put_contents($tempFile, json_encode([
            'type' => 'service_account',
            'project_id' => 'test-project',
            'private_key_id' => 'key-id',
            'private_key' => "-----BEGIN RSA PRIVATE KEY-----\nMIIEowIBAAKCAQEA2a2rwplBQLFMzb8H8g==\n-----END RSA PRIVATE KEY-----\n",
            'client_email' => 'test@test.iam.gserviceaccount.com',
            'client_id' => '123456789',
            'auth_uri' => 'https://accounts.google.com/o/oauth2/auth',
            'token_uri' => 'https://oauth2.googleapis.com/token',
            'auth_provider_x509_cert_url' => 'https://www.googleapis.com/oauth2/v1/certs',
            'client_x509_cert_url' => 'https://www.googleapis.com/robot/v1/metadata/x509/test',
        ]));

        $config = GoogleConfig::fromCredentialsPath($tempFile);

        $console = GoogleConsoleFactory::create($config);

        expect($console)->toBeInstanceOf(GoogleConsole::class);

        unlink($tempFile);
    });

    it('creates GoogleConsole from credentials path', function (): void {
        $tempFile = sys_get_temp_dir() . '/test-credentials-' . uniqid() . '.json';
        file_put_contents($tempFile, json_encode([
            'type' => 'service_account',
            'project_id' => 'test-project',
            'private_key_id' => 'key-id',
            'private_key' => "-----BEGIN RSA PRIVATE KEY-----\nMIIEowIBAAKCAQEA2a2rwplBQLFMzb8H8g==\n-----END RSA PRIVATE KEY-----\n",
            'client_email' => 'test@test.iam.gserviceaccount.com',
            'client_id' => '123456789',
            'auth_uri' => 'https://accounts.google.com/o/oauth2/auth',
            'token_uri' => 'https://oauth2.googleapis.com/token',
            'auth_provider_x509_cert_url' => 'https://www.googleapis.com/oauth2/v1/certs',
            'client_x509_cert_url' => 'https://www.googleapis.com/robot/v1/metadata/x509/test',
        ]));

        $console = GoogleConsoleFactory::fromCredentialsPath($tempFile);

        expect($console)->toBeInstanceOf(GoogleConsole::class);

        unlink($tempFile);
    });

    it('throws when credentials path does not exist', function (): void {
        GoogleConsoleFactory::fromCredentialsPath('/non/existent/credentials.json');
    })->throws(GoogleConsoleFailure::class, 'Credentials file not found');

    it('creates GoogleConsole from OAuth2 credentials path and refresh token', function (): void {
        $tempFile = sys_get_temp_dir() . '/test-oauth2-' . uniqid() . '.json';
        file_put_contents($tempFile, json_encode([
            'web' => [
                'client_id' => 'cid.apps.googleusercontent.com',
                'client_secret' => 'secret',
                'redirect_uris' => ['https://example.com/callback'],
            ],
        ]));

        $console = GoogleConsoleFactory::fromOAuth2RefreshToken(
            $tempFile,
            'refresh-token-123',
            fetchAccessToken: false,
        );

        expect($console)->toBeInstanceOf(GoogleConsole::class);

        unlink($tempFile);
    });

    it('creates GoogleConsole from OAuth2Config', function (): void {
        $config = new OAuth2Config(
            clientId: 'cid',
            clientSecret: 'sec',
            refreshToken: 'rt',
        );

        $console = GoogleConsoleFactory::fromOAuth2Config($config, fetchAccessToken: false);

        expect($console)->toBeInstanceOf(GoogleConsole::class);
    });

    it('throws when OAuth2 credentials path does not exist', function (): void {
        GoogleConsoleFactory::fromOAuth2RefreshToken('/non/existent/oauth.json', 'rt');
    })->throws(GoogleConsoleFailure::class, 'Credentials file not found');
});
