<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\Exception\GoogleConsoleFailure;
use Pekral\GoogleConsole\Exception\QuotaExceededException;

describe(QuotaExceededException::class, function (): void {

    it('extends GoogleConsoleFailure', function (): void {
        $exception = new QuotaExceededException('Quota exceeded');

        expect($exception)->toBeInstanceOf(GoogleConsoleFailure::class);
    });

    it('stores message and optional limit type and retry after', function (): void {
        $exception = new QuotaExceededException('Daily limit hit', 'qpd');

        expect($exception->getMessage())->toBe('Daily limit hit')
            ->and($exception->getLimitType())->toBe('qpd')
            ->and($exception->getRetryAfterSeconds())->toBeNull();
    });

    it('returns retry after seconds when provided', function (): void {
        $exception = new QuotaExceededException('Rate limit', 'qpm', 42);

        expect($exception->getRetryAfterSeconds())->toBe(42);
    });
});
