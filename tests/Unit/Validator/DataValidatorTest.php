<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\Exception\BatchSizeLimitExceeded;
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

    it('validates batch size within limit', function (): void {
        $validator = new DataValidator();

        $validator->validateBatchSize(50, 100);

        expect(true)->toBeTrue();
    });

    it('validates batch size at exact limit', function (): void {
        $validator = new DataValidator();

        $validator->validateBatchSize(100, 100);

        expect(true)->toBeTrue();
    });

    it('throws BatchSizeLimitExceeded when batch size exceeds limit', function (): void {
        $validator = new DataValidator();

        $validator->validateBatchSize(101, 100);
    })->throws(BatchSizeLimitExceeded::class, 'Batch size 101 exceeds maximum of 100 URLs');

    it('validates zero batch size', function (): void {
        $validator = new DataValidator();

        $validator->validateBatchSize(0, 100);

        expect(true)->toBeTrue();
    });
});
