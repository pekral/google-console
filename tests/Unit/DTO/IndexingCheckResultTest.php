<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\DTO\IndexingCheckResult;
use Pekral\GoogleConsole\Enum\IndexingCheckConfidence;
use Pekral\GoogleConsole\Enum\IndexingCheckReasonCode;
use Pekral\GoogleConsole\Enum\IndexingCheckSourceType;
use Pekral\GoogleConsole\Enum\IndexingCheckStatus;

describe(IndexingCheckResult::class, function (): void {

    it('creates result with all properties', function (): void {
        $checkedAt = new DateTimeImmutable('2024-01-15 10:30:00');

        $result = new IndexingCheckResult(
            primaryStatus: IndexingCheckStatus::INDEXED,
            confidence: IndexingCheckConfidence::HIGH,
            reasonCodes: [IndexingCheckReasonCode::INDEXED_CONFIRMED],
            checkedAt: $checkedAt,
            sourceType: IndexingCheckSourceType::AUTHORITATIVE,
        );

        expect($result->primaryStatus)->toBe(IndexingCheckStatus::INDEXED)
            ->and($result->confidence)->toBe(IndexingCheckConfidence::HIGH)
            ->and($result->reasonCodes)->toBe([IndexingCheckReasonCode::INDEXED_CONFIRMED])
            ->and($result->checkedAt)->toBe($checkedAt)
            ->and($result->sourceType)->toBe(IndexingCheckSourceType::AUTHORITATIVE)
            ->and($result->recommendations)->toBe([]);
    });

    it('converts to array with all properties', function (): void {
        $checkedAt = new DateTimeImmutable('2024-01-15T10:30:00+00:00');
        $recommendations = [
            'Check reason codes and fix blocking issues.',
            'Remove meta noindex or allow indexing in page meta tags.',
        ];

        $result = new IndexingCheckResult(
            primaryStatus: IndexingCheckStatus::NOT_INDEXED,
            confidence: IndexingCheckConfidence::HIGH,
            reasonCodes: [IndexingCheckReasonCode::NOT_INDEXED_CONFIRMED, IndexingCheckReasonCode::META_NOINDEX],
            checkedAt: $checkedAt,
            sourceType: IndexingCheckSourceType::AUTHORITATIVE,
            recommendations: $recommendations,
        );

        $array = $result->toArray();

        expect($array['primaryStatus'])->toBe('NOT_INDEXED')
            ->and($array['confidence'])->toBe('high')
            ->and($array['reason_codes'])->toBe(['NOT_INDEXED_CONFIRMED', 'META_NOINDEX'])
            ->and($array['checked_at'])->toBe('2024-01-15T10:30:00+00:00')
            ->and($array['source_type'])->toBe('authoritative')
            ->and($array['recommendations'])->toBe($recommendations);
    });
});
