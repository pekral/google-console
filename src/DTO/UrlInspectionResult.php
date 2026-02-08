<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\DTO;

use DateTimeImmutable;

final readonly class UrlInspectionResult
{

    public function __construct(
        public string $inspectionResultLink,
        public string $indexStatusResult,
        public string $verdict,
        public string $coverageState,
        public string $robotsTxtState,
        public string $indexingState,
        public ?DateTimeImmutable $lastCrawlTime,
        public string $pageFetchState,
        public ?string $crawledAs,
        public ?string $googleCanonical,
        public ?string $userCanonical,
        public bool $isMobileFriendly,
        public ?string $mobileUsabilityIssue,
        public ?IndexingCheckResult $indexingCheckResult = null,
    ) {
    }

    /**
     * @param array{
     *     inspectionResultLink?: string,
     *     indexStatusResult?: array{
     *         verdict?: string,
     *         coverageState?: string,
     *         robotsTxtState?: string,
     *         indexingState?: string,
     *         lastCrawlTime?: string,
     *         pageFetchState?: string,
     *         crawledAs?: string,
     *         googleCanonical?: string,
     *         userCanonical?: string
     *     },
     *     mobileUsabilityResult?: array{
     *         verdict?: string,
     *         issues?: array<array{
     *             issueType?: string
     *         }>
     *     },
     *     indexingCheckResult?: \Pekral\GoogleConsole\DTO\IndexingCheckResult
     * } $data
     */
    public static function fromApiResponse(array $data): self
    {
        $indexStatus = $data['indexStatusResult'] ?? [];
        $mobileUsability = $data['mobileUsabilityResult'] ?? [];

        $lastCrawlTime = null;

        if (isset($indexStatus['lastCrawlTime'])) {
            $lastCrawlTime = new DateTimeImmutable($indexStatus['lastCrawlTime']);
        }

        $mobileIssue = null;

        if (isset($mobileUsability['issues'][0]['issueType'])) {
            $mobileIssue = $mobileUsability['issues'][0]['issueType'];
        }

        return new self(
            inspectionResultLink: $data['inspectionResultLink'] ?? '',
            indexStatusResult: $indexStatus['verdict'] ?? 'VERDICT_UNSPECIFIED',
            verdict: $indexStatus['verdict'] ?? 'VERDICT_UNSPECIFIED',
            coverageState: $indexStatus['coverageState'] ?? '',
            robotsTxtState: $indexStatus['robotsTxtState'] ?? '',
            indexingState: $indexStatus['indexingState'] ?? '',
            lastCrawlTime: $lastCrawlTime,
            pageFetchState: $indexStatus['pageFetchState'] ?? '',
            crawledAs: $indexStatus['crawledAs'] ?? null,
            googleCanonical: $indexStatus['googleCanonical'] ?? null,
            userCanonical: $indexStatus['userCanonical'] ?? null,
            isMobileFriendly: ($mobileUsability['verdict'] ?? '') === 'PASS',
            mobileUsabilityIssue: $mobileIssue,
            indexingCheckResult: $data['indexingCheckResult'] ?? null,
        );
    }

    public function isIndexed(): bool
    {
        return $this->verdict === 'PASS';
    }

    public function isIndexable(): bool
    {
        return $this->indexingState === 'INDEXING_ALLOWED';
    }

    public function isCrawlable(): bool
    {
        return $this->robotsTxtState === 'ALLOWED';
    }

    /**
     * @return array{
     *     inspectionResultLink: string,
     *     verdict: string,
     *     coverageState: string,
     *     robotsTxtState: string,
     *     indexingState: string,
     *     lastCrawlTime: ?string,
     *     pageFetchState: string,
     *     crawledAs: ?string,
     *     googleCanonical: ?string,
     *     userCanonical: ?string,
     *     isMobileFriendly: bool,
     *     mobileUsabilityIssue: ?string,
     *     isIndexed: bool,
     *     isIndexable: bool,
     *     isCrawlable: bool,
     *     indexingCheckResult?: array{primaryStatus: string, confidence: string, reason_codes: list<string>, checked_at: string, source_type: string}
     * }
     */
    public function toArray(): array
    {
        $array = [
            'coverageState' => $this->coverageState,
            'crawledAs' => $this->crawledAs,
            'googleCanonical' => $this->googleCanonical,
            'indexingState' => $this->indexingState,
            'inspectionResultLink' => $this->inspectionResultLink,
            'isCrawlable' => $this->isCrawlable(),
            'isIndexable' => $this->isIndexable(),
            'isIndexed' => $this->isIndexed(),
            'isMobileFriendly' => $this->isMobileFriendly,
            'lastCrawlTime' => $this->lastCrawlTime?->format('c'),
            'mobileUsabilityIssue' => $this->mobileUsabilityIssue,
            'pageFetchState' => $this->pageFetchState,
            'robotsTxtState' => $this->robotsTxtState,
            'userCanonical' => $this->userCanonical,
            'verdict' => $this->verdict,
        ];

        if ($this->indexingCheckResult !== null) {
            $array['indexingCheckResult'] = $this->indexingCheckResult->toArray();
        }

        return $array;
    }

}
