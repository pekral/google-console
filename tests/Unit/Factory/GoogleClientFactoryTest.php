<?php

declare(strict_types = 1);

use Google\Client;
use Pekral\GoogleConsole\Config\GoogleConfig;
use Pekral\GoogleConsole\Config\OAuth2Config;
use Pekral\GoogleConsole\Factory\GoogleClientFactory;

describe(GoogleClientFactory::class, function (): void {

    it('creates client with credentials path', function (): void {
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

        $factory = new GoogleClientFactory();
        $config = GoogleConfig::fromCredentialsPath($tempFile);

        $client = $factory->create($config);

        expect($client)->toBeInstanceOf(Client::class);

        unlink($tempFile);
    });

    it('sets application name from config', function (): void {
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

        $factory = new GoogleClientFactory();
        $config = new GoogleConfig(credentialsPath: $tempFile, applicationName: 'Custom App Name');

        $client = $factory->create($config);

        expect($client->getConfig('application_name'))->toBe('Custom App Name');

        unlink($tempFile);
    });

    it('has indexing scope constant', function (): void {
        expect(GoogleClientFactory::INDEXING_SCOPE)->toBe('https://www.googleapis.com/auth/indexing');
    });

    it('adds indexing scope to client', function (): void {
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

        $factory = new GoogleClientFactory();
        $config = GoogleConfig::fromCredentialsPath($tempFile);

        $client = $factory->create($config);

        $scopes = $client->getScopes();

        expect($scopes)->toContain(GoogleClientFactory::INDEXING_SCOPE);

        unlink($tempFile);
    });

    it('creates client from OAuth2 config without fetching token', function (): void {
        $config = new OAuth2Config(
            clientId: 'cid.apps.googleusercontent.com',
            clientSecret: 'secret',
            refreshToken: 'refresh-token-123',
            redirectUri: 'https://example.com/callback',
        );

        $factory = new GoogleClientFactory();
        $client = $factory->createFromOAuth2($config, false);

        expect($client)->toBeInstanceOf(Client::class);

        $token = $client->getAccessToken();
        expect($token)->toBeArray()
            ->and($token['refresh_token'])->toBe('refresh-token-123');

        $scopes = $client->getScopes();
        expect($scopes)->toContain(GoogleClientFactory::INDEXING_SCOPE);
    });

    it('sets application name when creating from OAuth2 config', function (): void {
        $config = new OAuth2Config(
            clientId: 'cid',
            clientSecret: 'sec',
            refreshToken: 'rt',
            applicationName: 'My OAuth2 App',
        );

        $factory = new GoogleClientFactory();
        $client = $factory->createFromOAuth2($config, false);

        expect($client->getConfig('application_name'))->toBe('My OAuth2 App');
    });

    it('calls fetchAccessTokenWithRefreshToken when fetchAccessToken is true', function (): void {
        $config = new OAuth2Config(
            clientId: 'invalid-client-id.apps.googleusercontent.com',
            clientSecret: 'invalid-secret',
            refreshToken: 'invalid-refresh-token',
        );

        $factory = new GoogleClientFactory();
        $client = $factory->createFromOAuth2($config, true);

        expect($client)->toBeInstanceOf(Client::class);
    });
});
