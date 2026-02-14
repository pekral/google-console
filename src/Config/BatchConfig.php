<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\Config;

use Closure;

final readonly class BatchConfig
{

    public const int DEFAULT_MAX_BATCH_SIZE = 100;

    public const int DEFAULT_COOLDOWN_SECONDS = 5;

    public const int DEFAULT_MAX_RETRIES = 2;

    /**
     * @var \Closure(int): void
     */
    private Closure $sleepFunction;

    private Backoff $backoff;

    /**
     * @param \Closure(int): void|null $sleepFunction Custom sleep function (useful for testing)
     * @param \Pekral\GoogleConsole\Config\Backoff|null $backoff Custom backoff (default: exponential with jitter from cooldownSeconds)
     */
    public function __construct(
        public int $maxBatchSize = self::DEFAULT_MAX_BATCH_SIZE,
        public int $cooldownSeconds = self::DEFAULT_COOLDOWN_SECONDS,
        public int $maxRetries = self::DEFAULT_MAX_RETRIES,
        ?Closure $sleepFunction = null,
        ?Backoff $backoff = null,
    ) {
        $this->sleepFunction = $sleepFunction ?? static function (int $seconds): void {
            sleep($seconds);
        };
        $this->backoff = $backoff ?? new Backoff(baseSeconds: $cooldownSeconds, useJitter: true, sleepFunction: $this->sleepFunction);
    }

    public static function default(): self
    {
        return new self();
    }

    /**
     * Applies backoff before the next retry (exponential with jitter).
     *
     * @param int $attempt 1-based attempt number (1 = first retry after first failure)
     */
    public function applyCooldown(int $attempt = 1): void
    {
        $this->backoff->sleepBeforeRetry($attempt);
    }

}
