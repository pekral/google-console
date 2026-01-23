<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\Exception\GoogleConsoleFailure;

describe(GoogleConsoleFailure::class, function (): void {

    it('creates exception with message', function (): void {
        $exception = new GoogleConsoleFailure('Test error message');

        expect($exception->getMessage())->toBe('Test error message')
            ->and($exception->getCode())->toBe(0);
    });

    it('creates exception with message and code', function (): void {
        $exception = new GoogleConsoleFailure('Test error message', 500);

        expect($exception->getMessage())->toBe('Test error message')
            ->and($exception->getCode())->toBe(500);
    });

    it('creates exception with previous exception', function (): void {
        $previous = new RuntimeException('Previous error');
        $exception = new GoogleConsoleFailure('Test error message', 0, $previous);

        expect($exception->getPrevious())->toBe($previous);
    });

    it('extends Exception class', function (): void {
        $exception = new GoogleConsoleFailure('Test');

        expect($exception)->toBeInstanceOf(Throwable::class);
    });
});
