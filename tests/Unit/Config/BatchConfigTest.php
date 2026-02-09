<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\Config\BatchConfig;

describe(BatchConfig::class, function (): void {

    it('creates instance with default values', function (): void {
        $config = new BatchConfig();

        expect($config->maxBatchSize)->toBe(BatchConfig::DEFAULT_MAX_BATCH_SIZE)
            ->and($config->cooldownSeconds)->toBe(BatchConfig::DEFAULT_COOLDOWN_SECONDS)
            ->and($config->maxRetries)->toBe(BatchConfig::DEFAULT_MAX_RETRIES);
    });

    it('creates instance with custom values', function (): void {
        $config = new BatchConfig(
            maxBatchSize: 50,
            cooldownSeconds: 10,
            maxRetries: 5,
        );

        expect($config->maxBatchSize)->toBe(50)
            ->and($config->cooldownSeconds)->toBe(10)
            ->and($config->maxRetries)->toBe(5);
    });

    it('creates default instance via factory method', function (): void {
        $config = BatchConfig::default();

        expect($config->maxBatchSize)->toBe(100)
            ->and($config->cooldownSeconds)->toBe(5)
            ->and($config->maxRetries)->toBe(2);
    });

    it('applies cooldown using custom sleep function', function (): void {
        $tracker = new class () {

            private int $seconds = 0;

            public function record(int $seconds): void
            {
                $this->seconds = $seconds;
            }

            public function getSeconds(): int
            {
                return $this->seconds;
            }

        };
        $config = new BatchConfig(
            cooldownSeconds: 3,
            sleepFunction: static function (int $seconds) use ($tracker): void {
                $tracker->record($seconds);
            },
        );

        $config->applyCooldown();

        expect($tracker->getSeconds())->toBe(3);
    });

    it('applies cooldown with default sleep function', function (): void {
        $config = new BatchConfig(cooldownSeconds: 0);

        $config->applyCooldown();

        expect(true)->toBeTrue();
    });

    it('applies cooldown with configured seconds', function (): void {
        $tracker = new class () {

            private int $seconds = 0;

            public function record(int $seconds): void
            {
                $this->seconds = $seconds;
            }

            public function getSeconds(): int
            {
                return $this->seconds;
            }

        };
        $config = new BatchConfig(
            cooldownSeconds: 10,
            sleepFunction: static function (int $seconds) use ($tracker): void {
                $tracker->record($seconds);
            },
        );

        $config->applyCooldown();

        expect($tracker->getSeconds())->toBe(10);
    });
});
