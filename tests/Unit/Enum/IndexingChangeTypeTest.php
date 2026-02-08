<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\Enum\IndexingChangeType;
use Pekral\GoogleConsole\Enum\IndexingCheckStatus;

describe(IndexingChangeType::class, function (): void {

    it('has expected case values', function (): void {
        expect(IndexingChangeType::NEWLY_INDEXED->value)->toBe('NEWLY_INDEXED')
            ->and(IndexingChangeType::DROPPED_FROM_INDEX->value)->toBe('DROPPED_FROM_INDEX')
            ->and(IndexingChangeType::BECAME_UNKNOWN->value)->toBe('BECAME_UNKNOWN')
            ->and(IndexingChangeType::RECOVERED_FROM_UNKNOWN->value)->toBe('RECOVERED_FROM_UNKNOWN');
    });

    it('returns null when previous and current status are same', function (): void {
        expect(IndexingChangeType::between(IndexingCheckStatus::INDEXED, IndexingCheckStatus::INDEXED))->toBeNull()
            ->and(IndexingChangeType::between(IndexingCheckStatus::NOT_INDEXED, IndexingCheckStatus::NOT_INDEXED))->toBeNull()
            ->and(IndexingChangeType::between(IndexingCheckStatus::UNKNOWN, IndexingCheckStatus::UNKNOWN))->toBeNull();
    });

    it('returns DROPPED_FROM_INDEX when indexed becomes not indexed', function (): void {
        expect(IndexingChangeType::between(IndexingCheckStatus::INDEXED, IndexingCheckStatus::NOT_INDEXED))
            ->toBe(IndexingChangeType::DROPPED_FROM_INDEX);
    });

    it('returns NEWLY_INDEXED when not indexed becomes indexed', function (): void {
        expect(IndexingChangeType::between(IndexingCheckStatus::NOT_INDEXED, IndexingCheckStatus::INDEXED))
            ->toBe(IndexingChangeType::NEWLY_INDEXED);
    });

    it('returns BECAME_UNKNOWN when status becomes unknown', function (): void {
        expect(IndexingChangeType::between(IndexingCheckStatus::INDEXED, IndexingCheckStatus::UNKNOWN))
            ->toBe(IndexingChangeType::BECAME_UNKNOWN)
            ->and(IndexingChangeType::between(IndexingCheckStatus::NOT_INDEXED, IndexingCheckStatus::UNKNOWN))
            ->toBe(IndexingChangeType::BECAME_UNKNOWN);
    });

    it('returns RECOVERED_FROM_UNKNOWN when unknown becomes indexed or not indexed', function (): void {
        expect(IndexingChangeType::between(IndexingCheckStatus::UNKNOWN, IndexingCheckStatus::INDEXED))
            ->toBe(IndexingChangeType::RECOVERED_FROM_UNKNOWN)
            ->and(IndexingChangeType::between(IndexingCheckStatus::UNKNOWN, IndexingCheckStatus::NOT_INDEXED))
            ->toBe(IndexingChangeType::RECOVERED_FROM_UNKNOWN);
    });
});
