<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\Enum\ApiFamily;
use Pekral\GoogleConsole\Exception\QuotaExceededException;
use Pekral\GoogleConsole\RateLimit\QuotaConfig;
use Pekral\GoogleConsole\RateLimit\RateLimiter;

describe(RateLimiter::class, function (): void {

    it('consumes URL Inspection with site key', function (): void {
        $clock = static fn (): int => 0;
        $date = static fn (): string => '2026-02-14';
        $limiter = new RateLimiter($clock, $date);

        $limiter->consume(ApiFamily::URL_INSPECTION, 'https://example.com/');

        expect(true)->toBeTrue();
    });

    it('consumes Search Analytics with site key', function (): void {
        $clock = static fn (): int => 0;
        $date = static fn (): string => '2026-02-14';
        $limiter = new RateLimiter($clock, $date);

        $limiter->consume(ApiFamily::SEARCH_ANALYTICS, 'https://site.com/');

        expect(true)->toBeTrue();
    });

    it('consumes Indexing without site key', function (): void {
        $clock = static fn (): int => 0;
        $date = static fn (): string => '2026-02-14';
        $limiter = new RateLimiter($clock, $date);

        $limiter->consume(ApiFamily::INDEXING);

        expect(true)->toBeTrue();
    });

    it('consumes Other without site key', function (): void {
        $clock = static fn (): int => 0;
        $date = static fn (): string => '2026-02-14';
        $limiter = new RateLimiter($clock, $date);

        $limiter->consume(ApiFamily::OTHER);

        expect(true)->toBeTrue();
    });

    it('throws when URL Inspection daily budget exceeded', function (): void {
        $state = (object) ['callCount' => 0];
        $clock = static function () use ($state): int {
            $state->callCount++;

            return (int) floor(($state->callCount - 1) / QuotaConfig::URL_INSPECTION_QPM) * QuotaConfig::SECONDS_PER_MINUTE;
        };
        $date = static fn (): string => '2026-02-14';
        $limiter = new RateLimiter($clock, $date);
        $limit = QuotaConfig::URL_INSPECTION_QPD;

        for ($i = 0; $i < $limit; $i++) {
            $limiter->consume(ApiFamily::URL_INSPECTION, 'https://example.com/');
        }

        $limiter->consume(ApiFamily::URL_INSPECTION, 'https://example.com/');
    })->throws(QuotaExceededException::class, 'Daily quota exceeded');

    it('throws when Indexing daily budget exceeded', function (): void {
        $state = (object) ['callCount' => 0];
        $clock = static function () use ($state): int {
            $state->callCount++;

            return (int) floor(($state->callCount - 1) / QuotaConfig::INDEXING_QPM) * QuotaConfig::SECONDS_PER_MINUTE;
        };
        $date = static fn (): string => '2026-02-14';
        $limiter = new RateLimiter($clock, $date);
        $limit = QuotaConfig::INDEXING_QPD;

        for ($i = 0; $i < $limit; $i++) {
            $limiter->consume(ApiFamily::INDEXING);
        }

        $limiter->consume(ApiFamily::INDEXING);
    })->throws(QuotaExceededException::class, 'Daily quota exceeded');

    it('keys URL Inspection by site', function (): void {
        $state = (object) ['callCount' => 0];
        $clock = static function () use ($state): int {
            $state->callCount++;

            return (int) floor(($state->callCount - 1) / QuotaConfig::URL_INSPECTION_QPM) * QuotaConfig::SECONDS_PER_MINUTE;
        };
        $date = static fn (): string => '2026-02-14';
        $limiter = new RateLimiter($clock, $date);
        $limit = QuotaConfig::URL_INSPECTION_QPD;

        for ($i = 0; $i < $limit; $i++) {
            $limiter->consume(ApiFamily::URL_INSPECTION, 'https://site-a.com/');
        }

        $limiter->consume(ApiFamily::URL_INSPECTION, 'https://site-b.com/');

        expect(true)->toBeTrue();
    });

    it('throws when Search Analytics QPM exceeded', function (): void {
        $clock = static fn (): int => 0;
        $date = static fn (): string => '2026-02-14';
        $limiter = new RateLimiter($clock, $date);
        $limit = QuotaConfig::SEARCH_ANALYTICS_QPM;

        for ($i = 0; $i < $limit; $i++) {
            $limiter->consume(ApiFamily::SEARCH_ANALYTICS, 'https://example.com/');
        }

        $limiter->consume(ApiFamily::SEARCH_ANALYTICS, 'https://example.com/');
    })->throws(QuotaExceededException::class, 'Rate limit exceeded');
});
