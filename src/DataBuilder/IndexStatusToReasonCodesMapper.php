<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\DataBuilder;

use Pekral\GoogleConsole\Enum\IndexingCheckReasonCode;

/**
 * Maps URL Inspection API indexStatus fields to business reason codes
 * (Indexation & Coverage, Blocking & Rules, Detection Issues).
 */
final class IndexStatusToReasonCodesMapper
{

    private const string ROBOTS_ALLOWED = 'ALLOWED';

    /**
     * @param array{
     *     coverageState?: string,
     *     indexingState?: string,
     *     robotsTxtState?: string,
     *     pageFetchState?: string,
     *     googleCanonical?: string,
     *     userCanonical?: string
     * } $indexStatus
     * @return list<\Pekral\GoogleConsole\Enum\IndexingCheckReasonCode>
     */
    public function map(array $indexStatus): array
    {
        $reasons = [];

        $reasons = $this->appendRobotsReasons($indexStatus, $reasons);
        $reasons = $this->appendIndexingStateReasons($indexStatus, $reasons);
        $reasons = $this->appendPageFetchReasons($indexStatus, $reasons);
        $reasons = $this->appendCoverageStateReasons($indexStatus, $reasons);
        $reasons = $this->appendCanonicalReasons($indexStatus, $reasons);

        return $this->deduplicateByValue($reasons);
    }

    /**
     * @param list<\Pekral\GoogleConsole\Enum\IndexingCheckReasonCode> $reasons
     * @return list<\Pekral\GoogleConsole\Enum\IndexingCheckReasonCode>
     */
    private function deduplicateByValue(array $reasons): array
    {
        $seen = [];
        $out = [];

        foreach ($reasons as $code) {
            if (!isset($seen[$code->value])) {
                $seen[$code->value] = true;
                $out[] = $code;
            }
        }

        return $out;
    }

    /**
     * @param array{robotsTxtState?: string} $indexStatus
     * @param list<\Pekral\GoogleConsole\Enum\IndexingCheckReasonCode> $reasons
     * @return list<\Pekral\GoogleConsole\Enum\IndexingCheckReasonCode>
     */
    private function appendRobotsReasons(array $indexStatus, array $reasons): array
    {
        $robotsTxtState = $indexStatus['robotsTxtState'] ?? '';

        if ($robotsTxtState !== '' && $robotsTxtState !== self::ROBOTS_ALLOWED) {
            $reasons[] = IndexingCheckReasonCode::ROBOTS_BLOCKED;
        }

        return $reasons;
    }

    /**
     * @param array{indexingState?: string} $indexStatus
     * @param list<\Pekral\GoogleConsole\Enum\IndexingCheckReasonCode> $reasons
     * @return list<\Pekral\GoogleConsole\Enum\IndexingCheckReasonCode>
     */
    private function appendIndexingStateReasons(array $indexStatus, array $reasons): array
    {
        $indexingState = $indexStatus['indexingState'] ?? '';

        if (in_array($indexingState, ['BLOCKED_BY_META_TAG', 'BLOCKED_BY_NOINDEX', 'BLOCKED_BY_HTTP_HEADER'], true)) {
            $reasons[] = IndexingCheckReasonCode::META_NOINDEX;
        }

        return $reasons;
    }

    /**
     * @param array{pageFetchState?: string} $indexStatus
     * @param list<\Pekral\GoogleConsole\Enum\IndexingCheckReasonCode> $reasons
     * @return list<\Pekral\GoogleConsole\Enum\IndexingCheckReasonCode>
     */
    private function appendPageFetchReasons(array $indexStatus, array $reasons): array
    {
        $pageFetchState = $indexStatus['pageFetchState'] ?? '';

        if (in_array($pageFetchState, ['', 'SUCCESSFUL', 'PAGE_FETCH_STATE_UNSPECIFIED'], true)) {
            return $reasons;
        }

        $reasons[] = match ($pageFetchState) {
            'SOFT_404' => IndexingCheckReasonCode::SOFT_404_SUSPECTED,
            'REDIRECT_ERROR' => IndexingCheckReasonCode::REDIRECTED,
            'ACCESS_DENIED' => IndexingCheckReasonCode::AUTH_REQUIRED_OR_FAILED,
            'INTERNAL_CRAWL_ERROR' => IndexingCheckReasonCode::TIMEOUT,
            'BLOCKED_ROBOTS_TXT' => IndexingCheckReasonCode::ROBOTS_BLOCKED,
            default => IndexingCheckReasonCode::HTTP_STATUS_NOT_200,
        };

        return $reasons;
    }

    /**
     * @param array{coverageState?: string} $indexStatus
     * @param list<\Pekral\GoogleConsole\Enum\IndexingCheckReasonCode> $reasons
     * @return list<\Pekral\GoogleConsole\Enum\IndexingCheckReasonCode>
     */
    private function appendCoverageStateReasons(array $indexStatus, array $reasons): array
    {
        $coverageState = $indexStatus['coverageState'] ?? '';

        if ($coverageState === '') {
            return $reasons;
        }

        if (str_contains($coverageState, 'Crawled - not indexed')) {
            $reasons[] = IndexingCheckReasonCode::INDEXING_PENDING;
        }

        if (str_contains($coverageState, 'Duplicate') && str_contains($coverageState, 'without user-selected canonical')) {
            $reasons[] = IndexingCheckReasonCode::DUPLICATE_WITHOUT_CANONICAL;
        } elseif (str_contains($coverageState, 'Duplicate') && str_contains($coverageState, 'chose different')) {
            $reasons[] = IndexingCheckReasonCode::DUPLICATE_CANONICAL_OTHER;
        }

        if (stripos($coverageState, 'soft 404') !== false) {
            $reasons[] = IndexingCheckReasonCode::SOFT_404_SUSPECTED;
        }

        return $reasons;
    }

    /**
     * @param array{googleCanonical?: string, userCanonical?: string} $indexStatus
     * @param list<\Pekral\GoogleConsole\Enum\IndexingCheckReasonCode> $reasons
     * @return list<\Pekral\GoogleConsole\Enum\IndexingCheckReasonCode>
     */
    private function appendCanonicalReasons(array $indexStatus, array $reasons): array
    {
        $googleCanonical = trim((string) ($indexStatus['googleCanonical'] ?? ''));
        $userCanonical = trim((string) ($indexStatus['userCanonical'] ?? ''));

        if ($googleCanonical !== '' && $userCanonical !== '' && $googleCanonical !== $userCanonical) {
            $reasons[] = IndexingCheckReasonCode::CANONICAL_MISMATCH;
        }

        return $reasons;
    }

}
