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

    it('converts array of strings to string array', function (): void {
        expect(TypeHelper::toStringArray(['a', 'b', 'c']))->toBe(['a', 'b', 'c']);
    });

    it('converts array of mixed values to string array', function (): void {
        expect(TypeHelper::toStringArray([1, 2.5, 'test', true]))->toBe(['1', '2.5', 'test', '1']);
    });

    it('returns empty array for non-array values', function (): void {
        expect(TypeHelper::toStringArray(null))->toBe([])
            ->and(TypeHelper::toStringArray('string'))->toBe([])
            ->and(TypeHelper::toStringArray(123))->toBe([]);
    });

    it('returns empty string for non-scalar values in array', function (): void {
        expect(TypeHelper::toStringArray([['nested'], new stdClass()]))->toBe(['', '']);
    });

    it('handles Stringable objects in array', function (): void {
        $stringable = new class implements Stringable {

            public function __toString(): string
            {
                return 'stringable';
            }
        
        };

        expect(TypeHelper::toStringArray([$stringable]))->toBe(['stringable']);
    });
});
