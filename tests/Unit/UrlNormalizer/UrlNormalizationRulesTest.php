<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\UrlNormalizer\TrailingSlashMode;
use Pekral\GoogleConsole\UrlNormalizer\UrlNormalizationRules;

describe(UrlNormalizationRules::class, function (): void {

    it('creates defaults with fragment removal and preserve trailing slash', function (): void {
        $rules = UrlNormalizationRules::defaults();

        expect($rules->removeFragment)->toBeTrue()
            ->and($rules->trailingSlash)->toBe(TrailingSlashMode::PRESERVE)
            ->and($rules->stripUtmParams)->toBeFalse()
            ->and($rules->stripGclid)->toBeFalse();
    });

    it('creates forApiCalls with strip utm and gclid', function (): void {
        $rules = UrlNormalizationRules::forApiCalls();

        expect($rules->removeFragment)->toBeTrue()
            ->and($rules->trailingSlash)->toBe(TrailingSlashMode::PRESERVE)
            ->and($rules->stripUtmParams)->toBeTrue()
            ->and($rules->stripGclid)->toBeTrue();
    });

    it('accepts custom constructor values', function (): void {
        $rules = new UrlNormalizationRules(
            removeFragment: false,
            trailingSlash: TrailingSlashMode::REMOVE,
            stripUtmParams: true,
            stripGclid: true,
        );

        expect($rules->removeFragment)->toBeFalse()
            ->and($rules->trailingSlash)->toBe(TrailingSlashMode::REMOVE)
            ->and($rules->stripUtmParams)->toBeTrue()
            ->and($rules->stripGclid)->toBeTrue();
    });
});
