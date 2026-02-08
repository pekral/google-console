<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\UrlNormalizer\TrailingSlashMode;

describe(TrailingSlashMode::class, function (): void {

    it('has expected case values', function (): void {
        expect(TrailingSlashMode::PRESERVE->value)->toBe('preserve')
            ->and(TrailingSlashMode::ADD->value)->toBe('add')
            ->and(TrailingSlashMode::REMOVE->value)->toBe('remove');
    });
});
