<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\DTO\IndexingCheckResult;
use Pekral\GoogleConsole\DTO\UrlInspectionResult;
use Pekral\GoogleConsole\Enum\IndexingCheckConfidence;
use Pekral\GoogleConsole\Enum\IndexingCheckReasonCode;
use Pekral\GoogleConsole\Enum\IndexingCheckSourceType;
use Pekral\GoogleConsole\Enum\IndexingCheckStatus;

describe(IndexingCheckStatus::class, function (): void {

    it('has expected case values', function (): void {
        expect(IndexingCheckStatus::INDEXED->value)->toBe('INDEXED')
            ->and(IndexingCheckStatus::NOT_INDEXED->value)->toBe('NOT_INDEXED')
            ->and(IndexingCheckStatus::UNKNOWN->value)->toBe('UNKNOWN');
    });

    it('returns primary status from url inspection result when indexing check result is present', function (): void {
        $indexingCheckResult = new IndexingCheckResult(
            primaryStatus: IndexingCheckStatus::INDEXED,
            confidence: IndexingCheckConfidence::HIGH,
            reasonCodes: [IndexingCheckReasonCode::INDEXED_CONFIRMED],
            checkedAt: new DateTimeImmutable(),
            sourceType: IndexingCheckSourceType::AUTHORITATIVE,
        );

        $urlResult = new UrlInspectionResult(
            inspectionResultLink: '',
            indexStatusResult: 'PASS',
            verdict: 'PASS',
            coverageState: 'Indexed',
            robotsTxtState: 'ALLOWED',
            indexingState: 'INDEXING_ALLOWED',
            lastCrawlTime: null,
            pageFetchState: 'SUCCESSFUL',
            crawledAs: null,
            googleCanonical: null,
            userCanonical: null,
            isMobileFriendly: true,
            mobileUsabilityIssue: null,
            indexingCheckResult: $indexingCheckResult,
        );

        expect(IndexingCheckStatus::fromUrlInspectionResult($urlResult))->toBe(IndexingCheckStatus::INDEXED);
    });

    it('returns NOT_INDEXED from url inspection result when indexing check result is present', function (): void {
        $indexingCheckResult = new IndexingCheckResult(
            primaryStatus: IndexingCheckStatus::NOT_INDEXED,
            confidence: IndexingCheckConfidence::HIGH,
            reasonCodes: [IndexingCheckReasonCode::NOT_INDEXED_CONFIRMED],
            checkedAt: new DateTimeImmutable(),
            sourceType: IndexingCheckSourceType::AUTHORITATIVE,
        );

        $urlResult = new UrlInspectionResult(
            inspectionResultLink: '',
            indexStatusResult: 'FAIL',
            verdict: 'FAIL',
            coverageState: '',
            robotsTxtState: 'ALLOWED',
            indexingState: 'INDEXING_ALLOWED',
            lastCrawlTime: null,
            pageFetchState: 'SUCCESSFUL',
            crawledAs: null,
            googleCanonical: null,
            userCanonical: null,
            isMobileFriendly: true,
            mobileUsabilityIssue: null,
            indexingCheckResult: $indexingCheckResult,
        );

        expect(IndexingCheckStatus::fromUrlInspectionResult($urlResult))->toBe(IndexingCheckStatus::NOT_INDEXED);
    });

    it('returns INDEXED from verdict when indexing check result is null', function (): void {
        $urlResult = UrlInspectionResult::fromApiResponse([
            'inspectionResultLink' => '',
            'indexStatusResult' => ['verdict' => 'PASS', 'coverageState' => 'Indexed'],
            'mobileUsabilityResult' => ['verdict' => 'PASS', 'issues' => []],
        ]);

        expect(IndexingCheckStatus::fromUrlInspectionResult($urlResult))->toBe(IndexingCheckStatus::INDEXED);
    });

    it('returns NOT_INDEXED from verdict when indexing check result is null', function (): void {
        $urlResult = UrlInspectionResult::fromApiResponse([
            'inspectionResultLink' => '',
            'indexStatusResult' => ['verdict' => 'FAIL', 'coverageState' => ''],
            'mobileUsabilityResult' => ['verdict' => 'PASS', 'issues' => []],
        ]);

        expect(IndexingCheckStatus::fromUrlInspectionResult($urlResult))->toBe(IndexingCheckStatus::NOT_INDEXED);
    });

    it('returns UNKNOWN from verdict when indexing check result is null and verdict is unspecified', function (): void {
        $urlResult = UrlInspectionResult::fromApiResponse([
            'inspectionResultLink' => '',
            'indexStatusResult' => ['verdict' => 'VERDICT_UNSPECIFIED'],
            'mobileUsabilityResult' => [],
        ]);

        expect(IndexingCheckStatus::fromUrlInspectionResult($urlResult))->toBe(IndexingCheckStatus::UNKNOWN);
    });
});
