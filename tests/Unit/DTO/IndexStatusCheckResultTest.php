<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\DTO\IndexingCheckResult;
use Pekral\GoogleConsole\DTO\IndexStatusCheckResult;
use Pekral\GoogleConsole\Enum\IndexingCheckConfidence;
use Pekral\GoogleConsole\Enum\IndexingCheckReasonCode;
use Pekral\GoogleConsole\Enum\IndexingCheckSourceType;
use Pekral\GoogleConsole\Enum\IndexingCheckStatus;

describe(IndexStatusCheckResult::class, function (): void {

    it('fromIndexingCheckResult builds result with url and check fields', function (): void {
        $checkedAt = new DateTimeImmutable('2024-06-01 12:00:00');
        $check = new IndexingCheckResult(
            primaryStatus: IndexingCheckStatus::INDEXED,
            confidence: IndexingCheckConfidence::HIGH,
            reasonCodes: [IndexingCheckReasonCode::INDEXED_CONFIRMED],
            checkedAt: $checkedAt,
            sourceType: IndexingCheckSourceType::AUTHORITATIVE,
        );

        $result = IndexStatusCheckResult::fromIndexingCheckResult('https://example.com/page', $check);

        expect($result->url)->toBe('https://example.com/page')
            ->and($result->status)->toBe(IndexingCheckStatus::INDEXED)
            ->and($result->reasonCodes)->toBe([IndexingCheckReasonCode::INDEXED_CONFIRMED])
            ->and($result->confidence)->toBe(IndexingCheckConfidence::HIGH)
            ->and($result->checkedAt)->toBe($checkedAt)
            ->and($result->sourceType)->toBe(IndexingCheckSourceType::AUTHORITATIVE);
    });

    it('toArray returns expected keys and values', function (): void {
        $checkedAt = new DateTimeImmutable('2024-06-01 12:00:00');
        $check = new IndexingCheckResult(
            primaryStatus: IndexingCheckStatus::NOT_INDEXED,
            confidence: IndexingCheckConfidence::MEDIUM,
            reasonCodes: [IndexingCheckReasonCode::ROBOTS_BLOCKED, IndexingCheckReasonCode::META_NOINDEX],
            checkedAt: $checkedAt,
            sourceType: IndexingCheckSourceType::HEURISTIC,
        );
        $result = IndexStatusCheckResult::fromIndexingCheckResult('https://example.com/blocked', $check);

        $array = $result->toArray();

        expect($array['url'])->toBe('https://example.com/blocked')
            ->and($array['status'])->toBe('NOT_INDEXED')
            ->and($array['reason_codes'])->toBe(['ROBOTS_BLOCKED', 'META_NOINDEX'])
            ->and($array['confidence'])->toBe('medium')
            ->and($array['checked_at'])->toBe($checkedAt->format('c'))
            ->and($array['source_type'])->toBe('heuristic');
    });
});
