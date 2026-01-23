<?php

declare(strict_types = 1);

use Google\Client;
use Pekral\GoogleConsole\Config\GoogleConfig;
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
});
