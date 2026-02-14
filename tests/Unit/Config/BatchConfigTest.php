<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\Config\Backoff;
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
        $sleepFn = static function (int $seconds) use ($tracker): void {
            $tracker->record($seconds);
        };
        $config = new BatchConfig(
            cooldownSeconds: 3,
            sleepFunction: $sleepFn,
            backoff: new Backoff(baseSeconds: 3, useJitter: false, sleepFunction: $sleepFn),
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
        $sleepFn = static function (int $seconds) use ($tracker): void {
            $tracker->record($seconds);
        };
        $config = new BatchConfig(
            cooldownSeconds: 10,
            sleepFunction: $sleepFn,
            backoff: new Backoff(baseSeconds: 10, useJitter: false, sleepFunction: $sleepFn),
        );

        $config->applyCooldown();

        expect($tracker->getSeconds())->toBe(10);
    });

    it('applies exponential backoff for attempt 2', function (): void {
        $tracker = new class () {

            /**
             * @var list<int>
             */
            private array $calls = [];

            public function record(int $seconds): void
            {
                $this->calls[] = $seconds;
            }

            /**
             * @return list<int>
             */
            public function getCalls(): array
            {
                return $this->calls;
            }

        };
        $sleepFn = static function (int $seconds) use ($tracker): void {
            $tracker->record($seconds);
        };
        $config = new BatchConfig(
            cooldownSeconds: 5,
            sleepFunction: $sleepFn,
            backoff: new Backoff(baseSeconds: 5, useJitter: false, sleepFunction: $sleepFn),
        );

        $config->applyCooldown(1);
        $config->applyCooldown(2);

        expect($tracker->getCalls())->toBe([5, 10]);
    });

    it('uses default backoff with jitter when backoff not provided', function (): void {
        $tracker = new class () {

            /**
             * @var list<int>
             */
            private array $calls = [];

            public function record(int $seconds): void
            {
                $this->calls[] = $seconds;
            }

            /**
             * @return list<int>
             */
            public function getCalls(): array
            {
                return $this->calls;
            }

        };
        $config = new BatchConfig(
            cooldownSeconds: 5,
            maxRetries: 2,
            sleepFunction: static function (int $seconds) use ($tracker): void {
                $tracker->record($seconds);
            },
        );

        $config->applyCooldown(1);

        expect($tracker->getCalls())->toHaveCount(1)
            ->and($tracker->getCalls()[0])->toBeGreaterThanOrEqual(2)
            ->and($tracker->getCalls()[0])->toBeLessThanOrEqual(5);
    });

    it('invokes default sleep when no custom sleep function', function (): void {
        $start = microtime(true);
        $config = new BatchConfig(cooldownSeconds: 1);

        $config->applyCooldown(1);

        expect(microtime(true) - $start)->toBeGreaterThanOrEqual(0.5);
    });
});
