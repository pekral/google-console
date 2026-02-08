<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\DTO\IndexingChange;
use Pekral\GoogleConsole\DTO\IndexingCheckResult;
use Pekral\GoogleConsole\DTO\PerUrlInspectionResult;
use Pekral\GoogleConsole\DTO\UrlInspectionResult;
use Pekral\GoogleConsole\Enum\IndexingCheckConfidence;
use Pekral\GoogleConsole\Enum\IndexingCheckReasonCode;
use Pekral\GoogleConsole\Enum\IndexingCheckSourceType;
use Pekral\GoogleConsole\Enum\IndexingCheckStatus;
use Pekral\GoogleConsole\IndexingRunComparator;

describe(IndexingRunComparator::class, function (): void {

    it('returns empty changes when both runs have same statuses', function (): void {
        $url = 'https://example.com/page';
        $result = UrlInspectionResult::fromApiResponse([
            'indexStatusResult' => ['verdict' => 'PASS', 'coverageState' => 'Indexed'],
            'mobileUsabilityResult' => ['verdict' => 'PASS', 'issues' => []],
        ]);
        $perUrl = new PerUrlInspectionResult($url, IndexingCheckStatus::INDEXED, $result);

        $previous = [$url => $perUrl];
        $current = [$url => $perUrl];

        $comparator = new IndexingRunComparator();
        $comparison = $comparator->compare($previous, $current);

        expect($comparison->changes)->toBeEmpty()
            ->and($comparison->indexedDelta)->toBe(0)
            ->and($comparison->notIndexedDelta)->toBe(0)
            ->and($comparison->unknownDelta)->toBe(0);
    });

    it('detects DROPPED_FROM_INDEX when url was indexed and is now not indexed', function (): void {
        $url = 'https://example.com/dropped';
        $indexedResult = UrlInspectionResult::fromApiResponse([
            'indexStatusResult' => ['verdict' => 'PASS', 'coverageState' => 'Indexed'],
            'mobileUsabilityResult' => ['verdict' => 'PASS', 'issues' => []],
        ]);
        $notIndexedResult = UrlInspectionResult::fromApiResponse([
            'indexStatusResult' => ['verdict' => 'FAIL', 'coverageState' => ''],
            'mobileUsabilityResult' => ['verdict' => 'PASS', 'issues' => []],
        ]);

        $previous = [$url => new PerUrlInspectionResult($url, IndexingCheckStatus::INDEXED, $indexedResult)];
        $current = [$url => new PerUrlInspectionResult($url, IndexingCheckStatus::NOT_INDEXED, $notIndexedResult)];

        $comparator = new IndexingRunComparator();
        $comparison = $comparator->compare($previous, $current);

        expect($comparison->changes)->toHaveCount(1)
            ->and($comparison->changes[0]->url)->toBe($url)
            ->and($comparison->changes[0]->changeType->value)->toBe('DROPPED_FROM_INDEX')
            ->and($comparison->changes[0]->previousStatus)->toBe(IndexingCheckStatus::INDEXED)
            ->and($comparison->changes[0]->currentStatus)->toBe(IndexingCheckStatus::NOT_INDEXED)
            ->and($comparison->indexedDelta)->toBe(-1)
            ->and($comparison->notIndexedDelta)->toBe(1);
    });

    it('detects NEWLY_INDEXED when url was not indexed and is now indexed', function (): void {
        $url = 'https://example.com/new';
        $notIndexedResult = UrlInspectionResult::fromApiResponse([
            'indexStatusResult' => ['verdict' => 'FAIL', 'coverageState' => ''],
            'mobileUsabilityResult' => ['verdict' => 'PASS', 'issues' => []],
        ]);
        $indexedResult = UrlInspectionResult::fromApiResponse([
            'indexStatusResult' => ['verdict' => 'PASS', 'coverageState' => 'Indexed'],
            'mobileUsabilityResult' => ['verdict' => 'PASS', 'issues' => []],
        ]);

        $previous = [$url => new PerUrlInspectionResult($url, IndexingCheckStatus::NOT_INDEXED, $notIndexedResult)];
        $current = [$url => new PerUrlInspectionResult($url, IndexingCheckStatus::INDEXED, $indexedResult)];

        $comparator = new IndexingRunComparator();
        $comparison = $comparator->compare($previous, $current);

        expect($comparison->changes)->toHaveCount(1)
            ->and($comparison->changes[0]->changeType->value)->toBe('NEWLY_INDEXED')
            ->and($comparison->indexedDelta)->toBe(1)
            ->and($comparison->notIndexedDelta)->toBe(-1);
    });

    it('detects BECAME_UNKNOWN and RECOVERED_FROM_UNKNOWN', function (): void {
        $url1 = 'https://example.com/a';
        $url2 = 'https://example.com/b';
        $indexedResult = UrlInspectionResult::fromApiResponse([
            'indexStatusResult' => ['verdict' => 'PASS', 'coverageState' => 'Indexed'],
            'mobileUsabilityResult' => ['verdict' => 'PASS', 'issues' => []],
        ]);
        $unknownResult = UrlInspectionResult::fromApiResponse([
            'indexStatusResult' => ['verdict' => 'VERDICT_UNSPECIFIED'],
            'mobileUsabilityResult' => [],
        ]);

        $previous = [
            $url1 => new PerUrlInspectionResult($url1, IndexingCheckStatus::INDEXED, $indexedResult),
            $url2 => new PerUrlInspectionResult($url2, IndexingCheckStatus::UNKNOWN, $unknownResult),
        ];
        $current = [
            $url1 => new PerUrlInspectionResult($url1, IndexingCheckStatus::UNKNOWN, $unknownResult),
            $url2 => new PerUrlInspectionResult($url2, IndexingCheckStatus::INDEXED, $indexedResult),
        ];

        $comparator = new IndexingRunComparator();
        $comparison = $comparator->compare($previous, $current);

        expect($comparison->changes)->toHaveCount(2);
        $becameUnknown = array_values(array_filter($comparison->changes, fn (IndexingChange $c): bool => $c->changeType->value === 'BECAME_UNKNOWN'));
        $recovered = array_values(
            array_filter($comparison->changes, fn (IndexingChange $c): bool => $c->changeType->value === 'RECOVERED_FROM_UNKNOWN'),
        );
        expect($becameUnknown)->toHaveCount(1)->and($becameUnknown[0]->url)->toBe($url1);
        expect($recovered)->toHaveCount(1)->and($recovered[0]->url)->toBe($url2);
    });

    it('compares only overlapping urls and computes deltas for overlapping set', function (): void {
        $url = 'https://example.com/only';
        $result = UrlInspectionResult::fromApiResponse([
            'indexStatusResult' => ['verdict' => 'PASS', 'coverageState' => 'Indexed'],
            'mobileUsabilityResult' => ['verdict' => 'PASS', 'issues' => []],
        ]);
        $previous = [$url => new PerUrlInspectionResult($url, IndexingCheckStatus::INDEXED, $result)];
        $current = [$url => new PerUrlInspectionResult($url, IndexingCheckStatus::INDEXED, $result)];

        $comparator = new IndexingRunComparator();
        $comparison = $comparator->compare($previous, $current);

        expect($comparison->changes)->toBeEmpty()
            ->and($comparison->indexedDelta)->toBe(0)
            ->and($comparison->dominantReasonCodes)->toBeArray();
    });

    it('includes current reason codes in change when indexing check result is present', function (): void {
        $url = 'https://example.com/with-reasons';
        $indexingCheckResult = new IndexingCheckResult(
            primaryStatus: IndexingCheckStatus::NOT_INDEXED,
            confidence: IndexingCheckConfidence::HIGH,
            reasonCodes: [IndexingCheckReasonCode::NOT_INDEXED_CONFIRMED, IndexingCheckReasonCode::META_NOINDEX],
            checkedAt: new DateTimeImmutable(),
            sourceType: IndexingCheckSourceType::AUTHORITATIVE,
        );
        $urlResultWithReasons = new UrlInspectionResult(
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
        $indexedResult = UrlInspectionResult::fromApiResponse([
            'indexStatusResult' => ['verdict' => 'PASS', 'coverageState' => 'Indexed'],
            'mobileUsabilityResult' => ['verdict' => 'PASS', 'issues' => []],
        ]);
        $previous = [$url => new PerUrlInspectionResult($url, IndexingCheckStatus::INDEXED, $indexedResult)];
        $current = [$url => new PerUrlInspectionResult($url, IndexingCheckStatus::NOT_INDEXED, $urlResultWithReasons)];

        $comparator = new IndexingRunComparator();
        $comparison = $comparator->compare($previous, $current);

        expect($comparison->changes)->toHaveCount(1)
            ->and($comparison->changes[0]->currentReasonCodes)->toBe([
                IndexingCheckReasonCode::NOT_INDEXED_CONFIRMED,
                IndexingCheckReasonCode::META_NOINDEX,
            ]);
    });

    it('returns empty result when previous and current have no overlapping urls', function (): void {
        $result = UrlInspectionResult::fromApiResponse([
            'indexStatusResult' => ['verdict' => 'PASS', 'coverageState' => 'Indexed'],
            'mobileUsabilityResult' => ['verdict' => 'PASS', 'issues' => []],
        ]);
        $previous = ['https://example.com/a' => new PerUrlInspectionResult('https://example.com/a', IndexingCheckStatus::INDEXED, $result)];
        $current = ['https://example.com/b' => new PerUrlInspectionResult('https://example.com/b', IndexingCheckStatus::INDEXED, $result)];

        $comparator = new IndexingRunComparator();
        $comparison = $comparator->compare($previous, $current);

        expect($comparison->changes)->toBeEmpty()
            ->and($comparison->indexedDelta)->toBe(0)
            ->and($comparison->notIndexedDelta)->toBe(0)
            ->and($comparison->unknownDelta)->toBe(0)
            ->and($comparison->dominantReasonCodes)->toBeEmpty();
    });
});
