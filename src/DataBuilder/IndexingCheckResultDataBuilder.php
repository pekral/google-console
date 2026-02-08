<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\DataBuilder;

use DateTimeImmutable;
use Pekral\GoogleConsole\DTO\IndexingCheckResult;
use Pekral\GoogleConsole\Enum\IndexingCheckConfidence;
use Pekral\GoogleConsole\Enum\IndexingCheckReasonCode;
use Pekral\GoogleConsole\Enum\IndexingCheckSourceType;
use Pekral\GoogleConsole\Enum\IndexingCheckStatus;

final class IndexingCheckResultDataBuilder
{

    private const string VERDICT_PASS = 'PASS';

    private const string VERDICT_FAIL = 'FAIL';

    private const string VERDICT_UNSPECIFIED = 'VERDICT_UNSPECIFIED';

    private const string ROBOTS_ALLOWED = 'ALLOWED';

    private const string PAGE_FETCH_SUCCESSFUL = 'SUCCESSFUL';

    private const string INDEXING_STATE_BLOCKED_META = 'BLOCKED_BY_META_TAG';

    private const string INDEXING_STATE_BLOCKED_NOINDEX = 'BLOCKED_BY_NOINDEX';

    private const string COVERAGE_INDEXED_PHRASES = 'Submitted and indexed|Indexed|indexed';

    /**
     * Maps URL Inspection API index status to business IndexingCheckResult (SPEC section 4).
     *
     * @param array{
     *     coverageState?: string,
     *     indexingState?: string,
     *     robotsTxtState?: string,
     *     pageFetchState?: string,
     *     verdict?: string,
     *     lastCrawlTime?: string,
     *     googleCanonical?: string,
     *     userCanonical?: string
     * } $indexStatus
     */
    public function fromIndexStatusData(array $indexStatus, ?DateTimeImmutable $checkedAt = null): IndexingCheckResult
    {
        $checkedAt ??= new DateTimeImmutable();
        $verdict = $indexStatus['verdict'] ?? '';
        $coverageState = $indexStatus['coverageState'] ?? '';
        $indexingState = $indexStatus['indexingState'] ?? '';
        $robotsTxtState = $indexStatus['robotsTxtState'] ?? '';
        $pageFetchState = $indexStatus['pageFetchState'] ?? '';

        if ($this->isEmptyOrUnspecified($verdict, $indexStatus)) {
            return $this->createUnknownLow($checkedAt);
        }

        $exclusionReasons = $this->collectExclusionReasonCodes($robotsTxtState, $indexingState, $pageFetchState);

        if ($exclusionReasons !== []) {
            return $this->createNotIndexedWithReasons($checkedAt, $exclusionReasons);
        }

        if ($verdict === self::VERDICT_PASS && $this->isCoverageStateIndexed($coverageState)) {
            return $this->createIndexed($checkedAt);
        }

        if ($verdict === self::VERDICT_FAIL) {
            return $this->createNotIndexed($checkedAt);
        }

        return $this->createUnknownMedium($checkedAt);
    }

    private function createUnknownLow(DateTimeImmutable $checkedAt): IndexingCheckResult
    {
        return new IndexingCheckResult(
            primaryStatus: IndexingCheckStatus::UNKNOWN,
            confidence: IndexingCheckConfidence::LOW,
            reasonCodes: [IndexingCheckReasonCode::INSUFFICIENT_DATA],
            checkedAt: $checkedAt,
            sourceType: IndexingCheckSourceType::AUTHORITATIVE,
        );
    }

    /**
     * @param list<\Pekral\GoogleConsole\Enum\IndexingCheckReasonCode> $exclusionReasons
     */
    private function createNotIndexedWithReasons(DateTimeImmutable $checkedAt, array $exclusionReasons): IndexingCheckResult
    {
        return new IndexingCheckResult(
            primaryStatus: IndexingCheckStatus::NOT_INDEXED,
            confidence: IndexingCheckConfidence::HIGH,
            reasonCodes: [IndexingCheckReasonCode::NOT_INDEXED_CONFIRMED, ...$exclusionReasons],
            checkedAt: $checkedAt,
            sourceType: IndexingCheckSourceType::AUTHORITATIVE,
        );
    }

    private function createIndexed(DateTimeImmutable $checkedAt): IndexingCheckResult
    {
        return new IndexingCheckResult(
            primaryStatus: IndexingCheckStatus::INDEXED,
            confidence: IndexingCheckConfidence::HIGH,
            reasonCodes: [IndexingCheckReasonCode::INDEXED_CONFIRMED],
            checkedAt: $checkedAt,
            sourceType: IndexingCheckSourceType::AUTHORITATIVE,
        );
    }

    private function createNotIndexed(DateTimeImmutable $checkedAt): IndexingCheckResult
    {
        return new IndexingCheckResult(
            primaryStatus: IndexingCheckStatus::NOT_INDEXED,
            confidence: IndexingCheckConfidence::HIGH,
            reasonCodes: [IndexingCheckReasonCode::NOT_INDEXED_CONFIRMED],
            checkedAt: $checkedAt,
            sourceType: IndexingCheckSourceType::AUTHORITATIVE,
        );
    }

    private function createUnknownMedium(DateTimeImmutable $checkedAt): IndexingCheckResult
    {
        return new IndexingCheckResult(
            primaryStatus: IndexingCheckStatus::UNKNOWN,
            confidence: IndexingCheckConfidence::MEDIUM,
            reasonCodes: [IndexingCheckReasonCode::INSUFFICIENT_DATA],
            checkedAt: $checkedAt,
            sourceType: IndexingCheckSourceType::AUTHORITATIVE,
        );
    }

    /**
     * @param array{verdict?: string, coverageState?: string, indexingState?: string, robotsTxtState?: string, pageFetchState?: string} $indexStatus
     */
    private function isEmptyOrUnspecified(string $verdict, array $indexStatus): bool
    {
        if ($verdict !== '' && $verdict !== self::VERDICT_UNSPECIFIED) {
            return false;
        }

        $hasAny = ($indexStatus['coverageState'] ?? '') !== ''
            || ($indexStatus['indexingState'] ?? '') !== ''
            || ($indexStatus['robotsTxtState'] ?? '') !== ''
            || ($indexStatus['pageFetchState'] ?? '') !== '';

        return !$hasAny;
    }

    /**
     * @return list<\Pekral\GoogleConsole\Enum\IndexingCheckReasonCode>
     */
    private function collectExclusionReasonCodes(string $robotsTxtState, string $indexingState, string $pageFetchState): array
    {
        $reasons = [];

        if ($robotsTxtState !== '' && $robotsTxtState !== self::ROBOTS_ALLOWED) {
            $reasons[] = IndexingCheckReasonCode::ROBOTS_BLOCKED;
        }

        if ($indexingState === self::INDEXING_STATE_BLOCKED_META || $indexingState === self::INDEXING_STATE_BLOCKED_NOINDEX) {
            $reasons[] = IndexingCheckReasonCode::META_NOINDEX;
        }

        if ($pageFetchState !== '' && $pageFetchState !== self::PAGE_FETCH_SUCCESSFUL) {
            $reasons[] = IndexingCheckReasonCode::HTTP_STATUS_NOT_200;
        }

        return $reasons;
    }

    private function isCoverageStateIndexed(string $coverageState): bool
    {
        foreach (explode('|', self::COVERAGE_INDEXED_PHRASES) as $phrase) {
            if (str_contains($coverageState, $phrase)) {
                return true;
            }
        }

        return false;
    }

}
