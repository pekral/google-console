<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\RateLimit;

use Closure;
use Pekral\GoogleConsole\Exception\QuotaExceededException;

/**
 * In-memory token bucket for rate limiting (e.g. QPM or QPS).
 * Refills tokens at a fixed rate; consume(1) takes one token or throws.
 */
final class TokenBucket
{

    /**
     * @var array<string, array{tokens: float, lastRefillAt: int}>
     */
    private array $buckets = [];

    /**
     * @param \Closure(): int $clockSeconds Monotonic clock in seconds (for deterministic tests)
     */
    public function __construct(
        private readonly int $capacity,
        private readonly int $refillPeriodSeconds,
        private readonly int $refillAmount,
        private readonly Closure $clockSeconds,
    ) {
    }

    /**
     * Consumes one token for the given key. Throws if no token available.
     *
     * @throws \Pekral\GoogleConsole\Exception\QuotaExceededException When quota (per period) is exceeded
     */
    public function consume(string $key): void
    {
        $now = ($this->clockSeconds)();
        $bucket = $this->buckets[$key] ?? [
            'lastRefillAt' => $now,
            'tokens' => (float) $this->capacity,
        ];

        $elapsed = $now - $bucket['lastRefillAt'];
        $refills = (int) floor($elapsed / $this->refillPeriodSeconds);
        $newTokens = $bucket['tokens'] + $refills * $this->refillAmount;
        $tokens = min($this->capacity, $newTokens);
        $lastRefillAt = $bucket['lastRefillAt'] + $refills * $this->refillPeriodSeconds;

        if ($tokens < 1) {
            $retryAfter = $this->refillPeriodSeconds - ($now - $lastRefillAt);

            throw new QuotaExceededException(
                sprintf('Rate limit exceeded for key \'%s\' (quota per %d seconds)', $key, $this->refillPeriodSeconds),
                'qpm',
                max(1, $retryAfter),
            );
        }

        $this->buckets[$key] = [
            'lastRefillAt' => $lastRefillAt,
            'tokens' => $tokens - 1,
        ];
    }

}
