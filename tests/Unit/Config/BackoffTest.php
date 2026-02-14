<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\Config\Backoff;

describe(Backoff::class, function (): void {

    it('sleeps base seconds for attempt 1 without jitter', function (): void {
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
        $backoff = new Backoff(
            baseSeconds: 5,
            useJitter: false,
            sleepFunction: static function (int $seconds) use ($tracker): void {
                $tracker->record($seconds);
            },
        );

        $backoff->sleepBeforeRetry(1);

        expect($tracker->getCalls())->toBe([5]);
    });

    it('applies exponential delay for attempt 2 without jitter', function (): void {
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
        $backoff = new Backoff(baseSeconds: 3, useJitter: false, sleepFunction: $sleepFn);

        $backoff->sleepBeforeRetry(1);
        $backoff->sleepBeforeRetry(2);
        $backoff->sleepBeforeRetry(3);

        expect($tracker->getCalls())->toBe([3, 6, 12]);
    });

    it('does not sleep for attempt 0', function (): void {
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
        $backoff = new Backoff(
            baseSeconds: 5,
            useJitter: false,
            sleepFunction: static function (int $seconds) use ($tracker): void {
                $tracker->record($seconds);
            },
        );

        $backoff->sleepBeforeRetry(0);

        expect($tracker->getCalls())->toBe([]);
    });

    it('uses jitter factor when provided', function (): void {
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
        $backoff = new Backoff(
            baseSeconds: 10,
            useJitter: true,
            sleepFunction: static function (int $seconds) use ($tracker): void {
                $tracker->record($seconds);
            },
            jitterFactor: static fn (): float => 0.8,
        );

        $backoff->sleepBeforeRetry(1);

        expect($tracker->getCalls())->toBe([8]);
    });

    it('does not sleep when base seconds is zero', function (): void {
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
        $backoff = new Backoff(
            baseSeconds: 0,
            useJitter: false,
            sleepFunction: static function (int $seconds) use ($tracker): void {
                $tracker->record($seconds);
            },
        );

        $backoff->sleepBeforeRetry(1);

        expect($tracker->getCalls())->toBe([]);
    });

    it('applies random jitter when useJitter true and no jitter factor', function (): void {
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
        $backoff = new Backoff(
            baseSeconds: 10,
            useJitter: true,
            sleepFunction: static function (int $seconds) use ($tracker): void {
                $tracker->record($seconds);
            },
        );

        $backoff->sleepBeforeRetry(1);

        expect($tracker->getCalls())->toHaveCount(1)
            ->and($tracker->getCalls()[0])->toBeGreaterThanOrEqual(5)
            ->and($tracker->getCalls()[0])->toBeLessThanOrEqual(10);
    });

    it('uses default sleep when sleep function is null', function (): void {
        $backoff = new Backoff(baseSeconds: 0, useJitter: false);

        $backoff->sleepBeforeRetry(1);

        expect(true)->toBeTrue();
    });

    it('invokes default sleep for positive delay when sleep function is null', function (): void {
        $start = microtime(true);
        $backoff = new Backoff(baseSeconds: 1, useJitter: false);

        $backoff->sleepBeforeRetry(1);

        expect(microtime(true) - $start)->toBeGreaterThanOrEqual(1.0);
    });
});
