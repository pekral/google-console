<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\Config\GoogleConfig;
use Pekral\GoogleConsole\Exception\GoogleConsoleFailure;

describe(GoogleConfig::class, function (): void {

    it('creates config from credentials path', function (): void {
        $tempFile = sys_get_temp_dir() . '/test-credentials-' . uniqid() . '.json';
        file_put_contents($tempFile, '{}');

        $config = GoogleConfig::fromCredentialsPath($tempFile);

        expect($config->credentialsPath)->toBe($tempFile)
            ->and($config->applicationName)->toBe('Google Console Client');

        unlink($tempFile);
    });

    it('throws exception when credentials file not found', function (): void {
        GoogleConfig::fromCredentialsPath('/non/existent/path.json');
    })->throws(GoogleConsoleFailure::class, 'Credentials file not found: /non/existent/path.json');

    it('creates config with custom application name', function (): void {
        $tempFile = sys_get_temp_dir() . '/test-credentials-' . uniqid() . '.json';
        file_put_contents($tempFile, '{}');

        $config = new GoogleConfig(
            credentialsPath: $tempFile,
            applicationName: 'Custom App Name',
        );

        expect($config->credentialsPath)->toBe($tempFile)
            ->and($config->applicationName)->toBe('Custom App Name');

        unlink($tempFile);
    });
});
