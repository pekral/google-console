<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\Enum\Dimension;
use Pekral\GoogleConsole\Exception\GoogleConsoleFailure;

describe(Dimension::class, function (): void {

    it('has correct case values', function (): void {
        expect(Dimension::QUERY->value)->toBe('query')
            ->and(Dimension::PAGE->value)->toBe('page')
            ->and(Dimension::COUNTRY->value)->toBe('country')
            ->and(Dimension::DEVICE->value)->toBe('device')
            ->and(Dimension::SEARCH_APPEARANCE->value)->toBe('searchAppearance')
            ->and(Dimension::DATE->value)->toBe('date');
    });

    it('returns all values as string array', function (): void {
        $values = Dimension::values();

        expect($values)->toBe(['query', 'page', 'country', 'device', 'searchAppearance', 'date']);
    });

    it('creates dimension from valid string', function (): void {
        $dimension = Dimension::fromString('query');

        expect($dimension)->toBe(Dimension::QUERY);
    });

    it('throws exception for invalid dimension string', function (): void {
        Dimension::fromString('invalid');
    })->throws(GoogleConsoleFailure::class, 'Invalid dimension "invalid"');

    it('creates array of dimensions from string array', function (): void {
        $dimensions = Dimension::fromArray(['query', 'page', 'country']);

        expect($dimensions)->toBe([Dimension::QUERY, Dimension::PAGE, Dimension::COUNTRY]);
    });

    it('throws exception for invalid dimension in array', function (): void {
        Dimension::fromArray(['query', 'invalid']);
    })->throws(GoogleConsoleFailure::class, 'Invalid dimension "invalid"');

    it('converts dimensions to string array', function (): void {
        $strings = Dimension::toStringArray([Dimension::QUERY, Dimension::PAGE]);

        expect($strings)->toBe(['query', 'page']);
    });

    it('returns empty array when converting empty dimensions array', function (): void {
        $strings = Dimension::toStringArray([]);

        expect($strings)->toBe([]);
    });
});
