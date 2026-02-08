<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\DataBuilder\BatchAggregationBuilder;
use Pekral\GoogleConsole\DTO\IndexingCheckResult;
use Pekral\GoogleConsole\DTO\PerUrlInspectionResult;
use Pekral\GoogleConsole\DTO\UrlInspectionResult;
use Pekral\GoogleConsole\Enum\IndexingCheckConfidence;
use Pekral\GoogleConsole\Enum\IndexingCheckReasonCode;
use Pekral\GoogleConsole\Enum\IndexingCheckSourceType;
use Pekral\GoogleConsole\Enum\IndexingCheckStatus;

describe(BatchAggregationBuilder::class, function (): void {

    it('builds empty aggregation for empty results', function (): void {
        $builder = new BatchAggregationBuilder();
        $aggregation = $builder->build([]);

        expect($aggregation->indexedCount)->toBe(0)
            ->and($aggregation->notIndexedCount)->toBe(0)
            ->and($aggregation->unknownCount)->toBe(0)
            ->and($aggregation->reasonCodeCounts)->toBeEmpty();
    });

    it('counts indexed not indexed and unknown statuses', function (): void {
        $indexedResult = UrlInspectionResult::fromApiResponse([
            'indexStatusResult' => ['verdict' => 'PASS', 'coverageState' => 'Indexed'],
            'mobileUsabilityResult' => ['verdict' => 'PASS', 'issues' => []],
        ]);
        $notIndexedResult = UrlInspectionResult::fromApiResponse([
            'indexStatusResult' => ['verdict' => 'FAIL', 'coverageState' => ''],
            'mobileUsabilityResult' => ['verdict' => 'PASS', 'issues' => []],
        ]);
        $unknownResult = UrlInspectionResult::fromApiResponse([
            'indexStatusResult' => ['verdict' => 'VERDICT_UNSPECIFIED'],
            'mobileUsabilityResult' => [],
        ]);

        $items = [
            new PerUrlInspectionResult('https://example.com/a', IndexingCheckStatus::INDEXED, $indexedResult),
            new PerUrlInspectionResult('https://example.com/b', IndexingCheckStatus::NOT_INDEXED, $notIndexedResult),
            new PerUrlInspectionResult('https://example.com/c', IndexingCheckStatus::UNKNOWN, $unknownResult),
        ];

        $builder = new BatchAggregationBuilder();
        $aggregation = $builder->build($items);

        expect($aggregation->indexedCount)->toBe(1)
            ->and($aggregation->notIndexedCount)->toBe(1)
            ->and($aggregation->unknownCount)->toBe(1)
            ->and($aggregation->reasonCodeCounts)->toBeEmpty();
    });

    it('aggregates reason code counts from results with indexing check result', function (): void {
        $indexingCheckResult = new IndexingCheckResult(
            primaryStatus: IndexingCheckStatus::INDEXED,
            confidence: IndexingCheckConfidence::HIGH,
            reasonCodes: [
                IndexingCheckReasonCode::INDEXED_CONFIRMED,
                IndexingCheckReasonCode::CANONICAL_MISMATCH,
            ],
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

        $items = [
            new PerUrlInspectionResult('https://example.com/1', IndexingCheckStatus::INDEXED, $urlResult),
            new PerUrlInspectionResult('https://example.com/2', IndexingCheckStatus::INDEXED, $urlResult),
        ];

        $builder = new BatchAggregationBuilder();
        $aggregation = $builder->build($items);

        expect($aggregation->reasonCodeCounts)->toBe([
            'INDEXED_CONFIRMED' => 2,
            'CANONICAL_MISMATCH' => 2,
        ]);
    });
});
