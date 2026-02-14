<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\Exception\QuotaExceededException;
use Pekral\GoogleConsole\RateLimit\DailyBudget;

describe(DailyBudget::class, function (): void {

    it('allows consuming up to daily limit', function (): void {
        $date = static fn (): string => '2026-02-14';
        $budget = new DailyBudget(2, $date);

        $budget->consume('key1');
        $budget->consume('key1');

        expect(true)->toBeTrue();
    });

    it('throws when daily limit exceeded', function (): void {
        $date = static fn (): string => '2026-02-14';
        $budget = new DailyBudget(2, $date);

        $budget->consume('k');
        $budget->consume('k');
        $budget->consume('k');
    })->throws(QuotaExceededException::class, 'Daily quota exceeded for key \'k\'');

    it('resets on new date', function (): void {
        $state = (object) ['date' => '2026-02-14'];
        $date = static fn (): string => $state->date;
        $budget = new DailyBudget(1, $date);

        $budget->consume('k');
        $state->date = '2026-02-15';
        $budget->consume('k');

        expect(true)->toBeTrue();
    });

    it('tracks keys separately', function (): void {
        $date = static fn (): string => '2026-02-14';
        $budget = new DailyBudget(1, $date);

        $budget->consume('a');
        $budget->consume('b');
        $budget->consume('c');
        $budget->consume('a');
    })->throws(QuotaExceededException::class, 'Daily quota exceeded for key \'a\'');

    it('throws with limit type qpd', function (): void {
        $date = static fn (): string => '2026-02-14';
        $budget = new DailyBudget(1, $date);

        $budget->consume('x');

        try {
            $budget->consume('x');
        } catch (QuotaExceededException $e) {
            expect($e->getLimitType())->toBe('qpd')
                ->and($e->getRetryAfterSeconds())->toBeNull();
        }
    });
});
