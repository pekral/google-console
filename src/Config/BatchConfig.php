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

    /**
     * @param \Closure(int): void|null $sleepFunction Custom sleep function (useful for testing)
     */
    public function __construct(
        public int $maxBatchSize = self::DEFAULT_MAX_BATCH_SIZE,
        public int $cooldownSeconds = self::DEFAULT_COOLDOWN_SECONDS,
        public int $maxRetries = self::DEFAULT_MAX_RETRIES,
        ?Closure $sleepFunction = null,
    ) {
        $this->sleepFunction = $sleepFunction ?? static function (int $seconds): void {
            sleep($seconds);
        };
    }

    public static function default(): self
    {
        return new self();
    }

    public function applyCooldown(): void
    {
        ($this->sleepFunction)($this->cooldownSeconds);
    }

}
