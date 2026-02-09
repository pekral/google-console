<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\Exception\BatchSizeLimitExceeded;
use Pekral\GoogleConsole\Exception\GoogleConsoleFailure;

describe(BatchSizeLimitExceeded::class, function (): void {

    it('extends GoogleConsoleFailure', function (): void {
        $exception = new BatchSizeLimitExceeded(150, 100);

        expect($exception)->toBeInstanceOf(GoogleConsoleFailure::class);
    });

    it('contains descriptive message with url count and limit', function (): void {
        $exception = new BatchSizeLimitExceeded(150, 100);

        expect($exception->getMessage())->toBe('Batch size 150 exceeds maximum of 100 URLs');
    });

    it('contains correct counts in message', function (int $urlCount, int $maxBatchSize, string $expectedMessage): void {
        $exception = new BatchSizeLimitExceeded($urlCount, $maxBatchSize);

        expect($exception->getMessage())->toBe($expectedMessage);
    })->with([
        'slightly over' => [11, 10, 'Batch size 11 exceeds maximum of 10 URLs'],
        'significantly over' => [500, 50, 'Batch size 500 exceeds maximum of 50 URLs'],
    ]);
});
