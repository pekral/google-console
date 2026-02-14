<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\RateLimit;

use Closure;
use Pekral\GoogleConsole\Exception\QuotaExceededException;

/**
 * Per-key daily request counter. Resets implicitly when the date changes.
 */
final class DailyBudget
{

    /**
     * @var array<string, array{date: string, count: int}>
     */
    private array $budgets = [];

    /**
     * @param \Closure(): string $dateString Returns current date string (e.g. 'Y-m-d') for deterministic tests
     */
    public function __construct(private readonly int $dailyLimit, private readonly Closure $dateString) {
    }

    /**
     * Consumes one unit from the daily budget for the given key.
     *
     * @throws \Pekral\GoogleConsole\Exception\QuotaExceededException When daily quota is exceeded
     */
    public function consume(string $key): void
    {
        $today = ($this->dateString)();
        $budget = $this->budgets[$key] ?? ['date' => $today, 'count' => 0];

        if ($budget['date'] !== $today) {
            $budget = ['date' => $today, 'count' => 0];
        }

        if ($budget['count'] >= $this->dailyLimit) {
            throw new QuotaExceededException(
                sprintf('Daily quota exceeded for key \'%s\' (%d requests per day)', $key, $this->dailyLimit),
                'qpd',
            );
        }

        $this->budgets[$key] = [
            'count' => $budget['count'] + 1,
            'date' => $budget['date'],
        ];
    }

}
