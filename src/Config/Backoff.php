<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\Config;

use Closure;

/**
 * Exponential backoff with optional jitter for API retries.
 * Delay = baseSeconds * 2^(attempt-1), optionally multiplied by jitter factor (0.5–1.0).
 */
final readonly class Backoff
{

    /**
     * @var \Closure(int): void
     */
    private Closure $sleepFunction;

    public function __construct(
        public int $baseSeconds = 5,
        public bool $useJitter = true,
        ?Closure $sleepFunction = null,
        /**
         * @var \Closure(): float|null Returns value in [0.5, 1.0] for deterministic tests
         */
        private ?Closure $jitterFactor = null,
    ) {
        $this->sleepFunction = $sleepFunction ?? static function (int $seconds): void {
            sleep($seconds);
        };
    }

    /**
     * Sleeps for the backoff duration before the next retry.
     *
     * @param int $attempt 1-based attempt number (1 = first retry after first failure)
     */
    public function sleepBeforeRetry(int $attempt): void
    {
        $delay = $attempt < 1 ? 0 : $this->baseSeconds * 2 ** ($attempt - 1);

        if ($this->useJitter && $delay > 0) {
            $factor = $this->jitterFactor !== null
                ? ($this->jitterFactor)()
                : mt_rand(500, 1_000) / 1_000;
            $delay = (int) ceil($delay * $factor);
        }

        if ($delay > 0) {
            ($this->sleepFunction)($delay);
        }
    }

}
