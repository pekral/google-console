<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\Enum\FailureType;

describe(FailureType::class, function (): void {

    it('has hard failure type', function (): void {
        expect(FailureType::HARD->value)->toBe('HARD');
    });

    it('has soft failure type', function (): void {
        expect(FailureType::SOFT->value)->toBe('SOFT');
    });

    it('creates from string value', function (string $value, FailureType $expected): void {
        expect(FailureType::from($value))->toBe($expected);
    })->with([
        'hard' => ['HARD', FailureType::HARD],
        'soft' => ['SOFT', FailureType::SOFT],
    ]);
});
