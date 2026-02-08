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

    private const string COVERAGE_INDEXED_PHRASES = 'Submitted and indexed|Indexed|indexed';

    /**
     * @var array<\Pekral\GoogleConsole\Enum\IndexingCheckReasonCode>
     */
    private static array $blockingReasonCodes = [
        IndexingCheckReasonCode::ROBOTS_BLOCKED,
        IndexingCheckReasonCode::META_NOINDEX,
        IndexingCheckReasonCode::HTTP_STATUS_NOT_200,
        IndexingCheckReasonCode::SOFT_404_SUSPECTED,
        IndexingCheckReasonCode::REDIRECTED,
        IndexingCheckReasonCode::AUTH_REQUIRED_OR_FAILED,
        IndexingCheckReasonCode::TIMEOUT,
    ];

    public function __construct(private readonly IndexStatusToReasonCodesMapper $indexStatusToReasonCodesMapper = new IndexStatusToReasonCodesMapper()) {
    }

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

        if ($this->isEmptyOrUnspecified($verdict, $indexStatus)) {
            return $this->createUnknownLow($checkedAt);
        }

        $mappedReasons = $this->indexStatusToReasonCodesMapper->map($indexStatus);
        $hasBlockingReason = $this->hasAnyBlockingReason($mappedReasons);

        if ($hasBlockingReason) {
            return $this->createNotIndexedWithReasons($checkedAt, $mappedReasons);
        }

        if ($verdict === self::VERDICT_PASS && $this->isCoverageStateIndexed($coverageState)) {
            return $this->createIndexed($checkedAt, $mappedReasons);
        }

        if ($verdict === self::VERDICT_FAIL) {
            return $this->createNotIndexed($checkedAt, $mappedReasons);
        }

        return $this->createUnknownMedium($checkedAt, $mappedReasons);
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
     * @param list<\Pekral\GoogleConsole\Enum\IndexingCheckReasonCode> $mappedReasons
     */
    private function createNotIndexedWithReasons(DateTimeImmutable $checkedAt, array $mappedReasons): IndexingCheckResult
    {
        return new IndexingCheckResult(
            primaryStatus: IndexingCheckStatus::NOT_INDEXED,
            confidence: IndexingCheckConfidence::HIGH,
            reasonCodes: [IndexingCheckReasonCode::NOT_INDEXED_CONFIRMED, ...$mappedReasons],
            checkedAt: $checkedAt,
            sourceType: IndexingCheckSourceType::AUTHORITATIVE,
        );
    }

    /**
     * @param list<\Pekral\GoogleConsole\Enum\IndexingCheckReasonCode> $mappedReasons
     */
    private function createIndexed(DateTimeImmutable $checkedAt, array $mappedReasons = []): IndexingCheckResult
    {
        $supplementary = $this->filterNonBlockingReasons($mappedReasons);

        return new IndexingCheckResult(
            primaryStatus: IndexingCheckStatus::INDEXED,
            confidence: IndexingCheckConfidence::HIGH,
            reasonCodes: [IndexingCheckReasonCode::INDEXED_CONFIRMED, ...$supplementary],
            checkedAt: $checkedAt,
            sourceType: IndexingCheckSourceType::AUTHORITATIVE,
        );
    }

    /**
     * @param list<\Pekral\GoogleConsole\Enum\IndexingCheckReasonCode> $mappedReasons
     */
    private function createNotIndexed(DateTimeImmutable $checkedAt, array $mappedReasons = []): IndexingCheckResult
    {
        return new IndexingCheckResult(
            primaryStatus: IndexingCheckStatus::NOT_INDEXED,
            confidence: IndexingCheckConfidence::HIGH,
            reasonCodes: [IndexingCheckReasonCode::NOT_INDEXED_CONFIRMED, ...$mappedReasons],
            checkedAt: $checkedAt,
            sourceType: IndexingCheckSourceType::AUTHORITATIVE,
        );
    }

    /**
     * @param list<\Pekral\GoogleConsole\Enum\IndexingCheckReasonCode> $mappedReasons
     */
    private function createUnknownMedium(DateTimeImmutable $checkedAt, array $mappedReasons = []): IndexingCheckResult
    {
        $reasons = [IndexingCheckReasonCode::INSUFFICIENT_DATA];
        $seen = [IndexingCheckReasonCode::INSUFFICIENT_DATA->value => true];

        foreach ($mappedReasons as $code) {
            if (!isset($seen[$code->value])) {
                $seen[$code->value] = true;
                $reasons[] = $code;
            }
        }

        return new IndexingCheckResult(
            primaryStatus: IndexingCheckStatus::UNKNOWN,
            confidence: IndexingCheckConfidence::MEDIUM,
            reasonCodes: $reasons,
            checkedAt: $checkedAt,
            sourceType: IndexingCheckSourceType::AUTHORITATIVE,
        );
    }

    /**
     * @param list<\Pekral\GoogleConsole\Enum\IndexingCheckReasonCode> $reasons
     */
    private function hasAnyBlockingReason(array $reasons): bool
    {
        foreach ($reasons as $code) {
            if (in_array($code, self::$blockingReasonCodes, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<\Pekral\GoogleConsole\Enum\IndexingCheckReasonCode> $reasons
     * @return list<\Pekral\GoogleConsole\Enum\IndexingCheckReasonCode>
     */
    private function filterNonBlockingReasons(array $reasons): array
    {
        $out = [];

        foreach ($reasons as $code) {
            if (!in_array($code, self::$blockingReasonCodes, true)) {
                $out[] = $code;
            }
        }

        return $out;
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
