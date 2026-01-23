<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\Exception\GoogleConsoleFailure;
use Pekral\GoogleConsole\Validator\DataValidator;

describe(DataValidator::class, function (): void {

    it('validates valid dimensions', function (): void {
        $validator = new DataValidator();

        $validator->validateDimensions(['query']);
        $validator->validateDimensions(['query', 'page']);
        $validator->validateDimensions(['query', 'page', 'country', 'device']);

        expect(true)->toBeTrue();
    });

    it('throws exception for invalid dimension', function (): void {
        $validator = new DataValidator();

        $validator->validateDimensions(['invalid_dimension']);
    })->throws(GoogleConsoleFailure::class, 'Invalid dimension "invalid_dimension"');

    it('throws exception for mixed valid and invalid dimensions', function (): void {
        $validator = new DataValidator();

        $validator->validateDimensions(['query', 'invalid']);
    })->throws(GoogleConsoleFailure::class, 'Invalid dimension "invalid"');

    it('validates empty dimensions array', function (): void {
        $validator = new DataValidator();

        $validator->validateDimensions([]);

        expect(true)->toBeTrue();
    });
});
