<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\Helper\TypeHelper;

describe(TypeHelper::class, function (): void {

    it('converts numeric value to float', function (): void {
        expect(TypeHelper::toFloat(100))->toBe(100.0)
            ->and(TypeHelper::toFloat(100.5))->toBe(100.5)
            ->and(TypeHelper::toFloat('100'))->toBe(100.0)
            ->and(TypeHelper::toFloat('100.5'))->toBe(100.5);
    });

    it('returns zero for non-numeric values', function (): void {
        expect(TypeHelper::toFloat(null))->toBe(0.0)
            ->and(TypeHelper::toFloat('not a number'))->toBe(0.0)
            ->and(TypeHelper::toFloat([]))->toBe(0.0);
    });

    it('handles negative numbers', function (): void {
        expect(TypeHelper::toFloat(-100))->toBe(-100.0)
            ->and(TypeHelper::toFloat('-100.5'))->toBe(-100.5);
    });

    it('handles zero', function (): void {
        expect(TypeHelper::toFloat(0))->toBe(0.0)
            ->and(TypeHelper::toFloat('0'))->toBe(0.0);
    });
});
