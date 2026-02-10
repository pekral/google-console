<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\DataBuilder\IndexingCheckResultDataBuilder;
use Pekral\GoogleConsole\Enum\IndexingCheckConfidence;
use Pekral\GoogleConsole\Enum\IndexingCheckReasonCode;
use Pekral\GoogleConsole\Enum\IndexingCheckSourceType;
use Pekral\GoogleConsole\Enum\IndexingCheckStatus;
use Pekral\GoogleConsole\Enum\OperatingMode;

describe(IndexingCheckResultDataBuilder::class, function (): void {

    $mapper = new IndexingCheckResultDataBuilder();

    it('returns INDEXED with high confidence when verdict PASS and coverage state indexed', function () use ($mapper): void {
        $indexStatus = [
            'verdict' => 'PASS',
            'coverageState' => 'Submitted and indexed',
            'robotsTxtState' => 'ALLOWED',
            'indexingState' => 'INDEXING_ALLOWED',
            'pageFetchState' => 'SUCCESSFUL',
        ];

        $result = $mapper->fromIndexStatusData($indexStatus);

        expect($result->primaryStatus)->toBe(IndexingCheckStatus::INDEXED)
            ->and($result->confidence)->toBe(IndexingCheckConfidence::HIGH)
            ->and($result->reasonCodes)->toBe([IndexingCheckReasonCode::INDEXED_CONFIRMED])
            ->and($result->sourceType)->toBe(IndexingCheckSourceType::AUTHORITATIVE);
    });

    it('returns NOT_INDEXED with high confidence when verdict FAIL', function () use ($mapper): void {
        $indexStatus = [
            'verdict' => 'FAIL',
            'coverageState' => 'Not indexed',
            'robotsTxtState' => 'ALLOWED',
            'indexingState' => 'INDEXING_ALLOWED',
            'pageFetchState' => 'SUCCESSFUL',
        ];

        $result = $mapper->fromIndexStatusData($indexStatus);

        expect($result->primaryStatus)->toBe(IndexingCheckStatus::NOT_INDEXED)
            ->and($result->confidence)->toBe(IndexingCheckConfidence::HIGH)
            ->and($result->reasonCodes)->toContain(IndexingCheckReasonCode::NOT_INDEXED_CONFIRMED)
            ->and($result->sourceType)->toBe(IndexingCheckSourceType::AUTHORITATIVE);
    });

    it('returns NOT_INDEXED with ROBOTS_BLOCKED when robots txt disallows', function () use ($mapper): void {
        $indexStatus = [
            'verdict' => 'FAIL',
            'coverageState' => '',
            'robotsTxtState' => 'DISALLOWED',
            'indexingState' => 'INDEXING_ALLOWED',
            'pageFetchState' => 'SUCCESSFUL',
        ];

        $result = $mapper->fromIndexStatusData($indexStatus);

        expect($result->primaryStatus)->toBe(IndexingCheckStatus::NOT_INDEXED)
            ->and($result->reasonCodes)->toContain(IndexingCheckReasonCode::ROBOTS_BLOCKED)
            ->and($result->reasonCodes)->toContain(IndexingCheckReasonCode::NOT_INDEXED_CONFIRMED);
    });

    it('returns NOT_INDEXED with META_NOINDEX when blocked by meta tag', function () use ($mapper): void {
        $indexStatus = [
            'verdict' => 'FAIL',
            'coverageState' => '',
            'robotsTxtState' => 'ALLOWED',
            'indexingState' => 'BLOCKED_BY_META_TAG',
            'pageFetchState' => 'SUCCESSFUL',
        ];

        $result = $mapper->fromIndexStatusData($indexStatus);

        expect($result->primaryStatus)->toBe(IndexingCheckStatus::NOT_INDEXED)
            ->and($result->reasonCodes)->toContain(IndexingCheckReasonCode::META_NOINDEX)
            ->and($result->recommendations)->toContain('Remove meta noindex or allow indexing in page meta tags.');
    });

    it('returns NOT_INDEXED with META_NOINDEX when blocked by noindex', function () use ($mapper): void {
        $indexStatus = [
            'verdict' => 'FAIL',
            'coverageState' => '',
            'robotsTxtState' => 'ALLOWED',
            'indexingState' => 'BLOCKED_BY_NOINDEX',
            'pageFetchState' => 'SUCCESSFUL',
        ];

        $result = $mapper->fromIndexStatusData($indexStatus);

        expect($result->primaryStatus)->toBe(IndexingCheckStatus::NOT_INDEXED)
            ->and($result->reasonCodes)->toContain(IndexingCheckReasonCode::META_NOINDEX);
    });

    it('returns NOT_INDEXED with HTTP_STATUS_NOT_200 when page fetch not successful', function () use ($mapper): void {
        $indexStatus = [
            'verdict' => 'FAIL',
            'coverageState' => '',
            'robotsTxtState' => 'ALLOWED',
            'indexingState' => 'INDEXING_ALLOWED',
            'pageFetchState' => 'BLOCKED_4XX',
        ];

        $result = $mapper->fromIndexStatusData($indexStatus);

        expect($result->primaryStatus)->toBe(IndexingCheckStatus::NOT_INDEXED)
            ->and($result->reasonCodes)->toContain(IndexingCheckReasonCode::HTTP_STATUS_NOT_200);
    });

    it('returns UNKNOWN with INSUFFICIENT_DATA when data empty', function () use ($mapper): void {
        $result = $mapper->fromIndexStatusData([]);

        expect($result->primaryStatus)->toBe(IndexingCheckStatus::UNKNOWN)
            ->and($result->confidence)->toBe(IndexingCheckConfidence::LOW)
            ->and($result->reasonCodes)->toBe([IndexingCheckReasonCode::INSUFFICIENT_DATA])
            ->and($result->sourceType)->toBe(IndexingCheckSourceType::AUTHORITATIVE);
    });

    it('returns UNKNOWN with INSUFFICIENT_DATA when verdict unspecified and no other data', function () use ($mapper): void {
        $result = $mapper->fromIndexStatusData(['verdict' => 'VERDICT_UNSPECIFIED']);

        expect($result->primaryStatus)->toBe(IndexingCheckStatus::UNKNOWN)
            ->and($result->reasonCodes)->toBe([IndexingCheckReasonCode::INSUFFICIENT_DATA]);
    });

    it('returns UNKNOWN when verdict neither PASS nor FAIL and coverage not indexed', function () use ($mapper): void {
        $indexStatus = [
            'verdict' => 'PARTIAL',
            'coverageState' => 'Crawled - not indexed',
            'robotsTxtState' => 'ALLOWED',
            'indexingState' => 'INDEXING_ALLOWED',
            'pageFetchState' => 'SUCCESSFUL',
        ];

        $result = $mapper->fromIndexStatusData($indexStatus);

        expect($result->primaryStatus)->toBe(IndexingCheckStatus::UNKNOWN)
            ->and($result->confidence)->toBe(IndexingCheckConfidence::MEDIUM)
            ->and($result->reasonCodes)->toContain(IndexingCheckReasonCode::INSUFFICIENT_DATA)
            ->and($result->reasonCodes)->toContain(IndexingCheckReasonCode::INDEXING_PENDING);
    });

    it('returns NOT_INDEXED with META_NOINDEX when blocked by http header', function () use ($mapper): void {
        $indexStatus = [
            'verdict' => 'FAIL',
            'coverageState' => '',
            'robotsTxtState' => 'ALLOWED',
            'indexingState' => 'BLOCKED_BY_HTTP_HEADER',
            'pageFetchState' => 'SUCCESSFUL',
        ];

        $result = $mapper->fromIndexStatusData($indexStatus);

        expect($result->primaryStatus)->toBe(IndexingCheckStatus::NOT_INDEXED)
            ->and($result->reasonCodes)->toContain(IndexingCheckReasonCode::META_NOINDEX);
    });

    it('returns NOT_INDEXED with SOFT_404_SUSPECTED when page fetch state is SOFT_404', function () use ($mapper): void {
        $indexStatus = [
            'verdict' => 'FAIL',
            'coverageState' => '',
            'robotsTxtState' => 'ALLOWED',
            'indexingState' => 'INDEXING_ALLOWED',
            'pageFetchState' => 'SOFT_404',
        ];

        $result = $mapper->fromIndexStatusData($indexStatus);

        expect($result->primaryStatus)->toBe(IndexingCheckStatus::NOT_INDEXED)
            ->and($result->reasonCodes)->toContain(IndexingCheckReasonCode::SOFT_404_SUSPECTED);
    });

    it('returns NOT_INDEXED with REDIRECTED when page fetch state is REDIRECT_ERROR', function () use ($mapper): void {
        $indexStatus = [
            'verdict' => 'FAIL',
            'coverageState' => '',
            'robotsTxtState' => 'ALLOWED',
            'indexingState' => 'INDEXING_ALLOWED',
            'pageFetchState' => 'REDIRECT_ERROR',
        ];

        $result = $mapper->fromIndexStatusData($indexStatus);

        expect($result->primaryStatus)->toBe(IndexingCheckStatus::NOT_INDEXED)
            ->and($result->reasonCodes)->toContain(IndexingCheckReasonCode::REDIRECTED);
    });

    it('returns INDEXED with CANONICAL_MISMATCH when indexed but google and user canonical differ', function () use ($mapper): void {
        $indexStatus = [
            'verdict' => 'PASS',
            'coverageState' => 'Submitted and indexed',
            'robotsTxtState' => 'ALLOWED',
            'indexingState' => 'INDEXING_ALLOWED',
            'pageFetchState' => 'SUCCESSFUL',
            'googleCanonical' => 'https://example.com/a',
            'userCanonical' => 'https://example.com/b',
        ];

        $result = $mapper->fromIndexStatusData($indexStatus);

        expect($result->primaryStatus)->toBe(IndexingCheckStatus::INDEXED)
            ->and($result->reasonCodes)->toContain(IndexingCheckReasonCode::INDEXED_CONFIRMED)
            ->and($result->reasonCodes)->toContain(IndexingCheckReasonCode::CANONICAL_MISMATCH);
    });

    it('uses provided checked_at when given', function () use ($mapper): void {
        $checkedAt = new DateTimeImmutable('2024-06-01 12:00:00');
        $result = $mapper->fromIndexStatusData(
            ['verdict' => 'PASS', 'coverageState' => 'Submitted and indexed'],
            $checkedAt,
        );

        expect($result->checkedAt)->toBe($checkedAt)
            ->and($result->checkedAt->format('Y-m-d H:i:s'))->toBe('2024-06-01 12:00:00');
    });

    it('returns UNKNOWN in strict mode when verdict neither PASS nor FAIL but coverage indexed', function () use ($mapper): void {
        $indexStatus = [
            'verdict' => 'PARTIAL',
            'coverageState' => 'Submitted and indexed',
            'robotsTxtState' => 'ALLOWED',
            'indexingState' => 'INDEXING_ALLOWED',
            'pageFetchState' => 'SUCCESSFUL',
        ];

        $result = $mapper->fromIndexStatusData($indexStatus, null, OperatingMode::STRICT);

        expect($result->primaryStatus)->toBe(IndexingCheckStatus::UNKNOWN)
            ->and($result->sourceType)->toBe(IndexingCheckSourceType::AUTHORITATIVE);
    });

    it(
        'returns INDEXED with heuristic source and HEURISTIC_ONLY in best-effort when verdict not PASS but coverage indexed',
        function () use ($mapper): void {
            $indexStatus = [
                'verdict' => 'PARTIAL',
                'coverageState' => 'Submitted and indexed',
                'robotsTxtState' => 'ALLOWED',
                'indexingState' => 'INDEXING_ALLOWED',
                'pageFetchState' => 'SUCCESSFUL',
            ];
    
            $result = $mapper->fromIndexStatusData($indexStatus, null, OperatingMode::BEST_EFFORT);
    
            expect($result->primaryStatus)->toBe(IndexingCheckStatus::INDEXED)
                ->and($result->confidence)->toBe(IndexingCheckConfidence::MEDIUM)
                ->and($result->sourceType)->toBe(IndexingCheckSourceType::HEURISTIC)
                ->and($result->reasonCodes)->toContain(IndexingCheckReasonCode::HEURISTIC_ONLY);
        },
    );
});
