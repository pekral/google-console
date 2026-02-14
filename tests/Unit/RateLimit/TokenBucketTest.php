<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\Exception\QuotaExceededException;
use Pekral\GoogleConsole\RateLimit\TokenBucket;

describe(TokenBucket::class, function (): void {

    it('allows consuming up to capacity', function (): void {
        $clock = static fn (): int => 0;
        $bucket = new TokenBucket(2, 60, 2, $clock);

        $bucket->consume('key1');
        $bucket->consume('key1');

        expect(true)->toBeTrue();
    });

    it('throws when consuming beyond capacity', function (): void {
        $clock = static fn (): int => 0;
        $bucket = new TokenBucket(2, 60, 2, $clock);

        $bucket->consume('key1');
        $bucket->consume('key1');
        $bucket->consume('key1');
    })->throws(QuotaExceededException::class, 'Rate limit exceeded for key \'key1\'');

    it('refills after period', function (): void {
        $state = (object) ['time' => 0];
        $clock = static fn (): int => $state->time;
        $bucket = new TokenBucket(2, 60, 2, $clock);

        $bucket->consume('k');
        $bucket->consume('k');
        $state->time = 60;
        $bucket->consume('k');
        $bucket->consume('k');
        $state->time = 120;
        $bucket->consume('k');

        expect(true)->toBeTrue();
    });

    it('tracks keys separately', function (): void {
        $clock = static fn (): int => 0;
        $bucket = new TokenBucket(1, 60, 1, $clock);

        $bucket->consume('a');
        $bucket->consume('b');
        $bucket->consume('c');
        $bucket->consume('a');
    })->throws(QuotaExceededException::class);

    it('throws with retry after seconds', function (): void {
        $clock = static fn (): int => 0;
        $bucket = new TokenBucket(1, 60, 1, $clock);

        $bucket->consume('x');

        try {
            $bucket->consume('x');
        } catch (QuotaExceededException $e) {
            expect($e->getLimitType())->toBe('qpm')
                ->and($e->getRetryAfterSeconds())->toBeGreaterThanOrEqual(1)
                ->and($e->getRetryAfterSeconds())->toBeLessThanOrEqual(60);
        }
    });
});
