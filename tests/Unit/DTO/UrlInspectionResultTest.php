<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\DTO\UrlInspectionResult;

describe(UrlInspectionResult::class, function (): void {

    it('creates result from full api response', function (): void {
        $data = [
            'inspectionResultLink' => 'https://search.google.com/search-console/inspect',
            'indexStatusResult' => [
                'verdict' => 'PASS',
                'coverageState' => 'Submitted and indexed',
                'robotsTxtState' => 'ALLOWED',
                'indexingState' => 'INDEXING_ALLOWED',
                'lastCrawlTime' => '2024-01-15T10:30:00Z',
                'pageFetchState' => 'SUCCESSFUL',
                'crawledAs' => 'MOBILE',
                'googleCanonical' => 'https://example.com/page',
                'userCanonical' => 'https://example.com/page',
            ],
            'mobileUsabilityResult' => [
                'verdict' => 'PASS',
                'issues' => [],
            ],
        ];

        $result = UrlInspectionResult::fromApiResponse($data);

        expect($result->inspectionResultLink)->toBe('https://search.google.com/search-console/inspect')
            ->and($result->verdict)->toBe('PASS')
            ->and($result->coverageState)->toBe('Submitted and indexed')
            ->and($result->robotsTxtState)->toBe('ALLOWED')
            ->and($result->indexingState)->toBe('INDEXING_ALLOWED')
            ->and($result->lastCrawlTime)->not->toBeNull()
            ->and($result->lastCrawlTime?->format('Y-m-d'))->toBe('2024-01-15')
            ->and($result->pageFetchState)->toBe('SUCCESSFUL')
            ->and($result->crawledAs)->toBe('MOBILE')
            ->and($result->googleCanonical)->toBe('https://example.com/page')
            ->and($result->userCanonical)->toBe('https://example.com/page')
            ->and($result->isMobileFriendly)->toBeTrue()
            ->and($result->mobileUsabilityIssue)->toBeNull();
    });

    it('creates result with default values for missing data', function (): void {
        $result = UrlInspectionResult::fromApiResponse([]);

        expect($result->inspectionResultLink)->toBe('')
            ->and($result->verdict)->toBe('VERDICT_UNSPECIFIED')
            ->and($result->coverageState)->toBe('')
            ->and($result->robotsTxtState)->toBe('')
            ->and($result->indexingState)->toBe('')
            ->and($result->lastCrawlTime)->toBeNull()
            ->and($result->pageFetchState)->toBe('')
            ->and($result->crawledAs)->toBeNull()
            ->and($result->googleCanonical)->toBeNull()
            ->and($result->userCanonical)->toBeNull()
            ->and($result->isMobileFriendly)->toBeFalse()
            ->and($result->mobileUsabilityIssue)->toBeNull();
    });

    it('captures mobile usability issue', function (): void {
        $data = [
            'mobileUsabilityResult' => [
                'verdict' => 'FAIL',
                'issues' => [
                    ['issueType' => 'TEXT_TOO_SMALL'],
                    ['issueType' => 'CLICKABLE_ELEMENTS_TOO_CLOSE'],
                ],
            ],
        ];

        $result = UrlInspectionResult::fromApiResponse($data);

        expect($result->isMobileFriendly)->toBeFalse()
            ->and($result->mobileUsabilityIssue)->toBe('TEXT_TOO_SMALL');
    });

    it('returns true for isIndexed when verdict is PASS', function (): void {
        $data = [
            'indexStatusResult' => [
                'verdict' => 'PASS',
            ],
        ];

        $result = UrlInspectionResult::fromApiResponse($data);

        expect($result->isIndexed())->toBeTrue();
    });

    it('returns false for isIndexed when verdict is not PASS', function (): void {
        $data = [
            'indexStatusResult' => [
                'verdict' => 'FAIL',
            ],
        ];

        $result = UrlInspectionResult::fromApiResponse($data);

        expect($result->isIndexed())->toBeFalse();
    });

    it('returns true for isIndexable when indexing is allowed', function (): void {
        $data = [
            'indexStatusResult' => [
                'indexingState' => 'INDEXING_ALLOWED',
            ],
        ];

        $result = UrlInspectionResult::fromApiResponse($data);

        expect($result->isIndexable())->toBeTrue();
    });

    it('returns false for isIndexable when indexing is not allowed', function (): void {
        $data = [
            'indexStatusResult' => [
                'indexingState' => 'BLOCKED_BY_META_TAG',
            ],
        ];

        $result = UrlInspectionResult::fromApiResponse($data);

        expect($result->isIndexable())->toBeFalse();
    });

    it('returns true for isCrawlable when robots txt allows', function (): void {
        $data = [
            'indexStatusResult' => [
                'robotsTxtState' => 'ALLOWED',
            ],
        ];

        $result = UrlInspectionResult::fromApiResponse($data);

        expect($result->isCrawlable())->toBeTrue();
    });

    it('returns false for isCrawlable when robots txt blocks', function (): void {
        $data = [
            'indexStatusResult' => [
                'robotsTxtState' => 'DISALLOWED',
            ],
        ];

        $result = UrlInspectionResult::fromApiResponse($data);

        expect($result->isCrawlable())->toBeFalse();
    });

    it('converts to array with all properties', function (): void {
        $data = [
            'inspectionResultLink' => 'https://search.google.com/search-console/inspect',
            'indexStatusResult' => [
                'verdict' => 'PASS',
                'coverageState' => 'Submitted and indexed',
                'robotsTxtState' => 'ALLOWED',
                'indexingState' => 'INDEXING_ALLOWED',
                'lastCrawlTime' => '2024-01-15T10:30:00Z',
                'pageFetchState' => 'SUCCESSFUL',
                'crawledAs' => 'MOBILE',
                'googleCanonical' => 'https://example.com/page',
                'userCanonical' => 'https://example.com/page',
            ],
            'mobileUsabilityResult' => [
                'verdict' => 'PASS',
            ],
        ];

        $result = UrlInspectionResult::fromApiResponse($data);
        $array = $result->toArray();

        expect($array['inspectionResultLink'])->toBe('https://search.google.com/search-console/inspect')
            ->and($array['verdict'])->toBe('PASS')
            ->and($array['coverageState'])->toBe('Submitted and indexed')
            ->and($array['robotsTxtState'])->toBe('ALLOWED')
            ->and($array['indexingState'])->toBe('INDEXING_ALLOWED')
            ->and($array['lastCrawlTime'])->toBe('2024-01-15T10:30:00+00:00')
            ->and($array['pageFetchState'])->toBe('SUCCESSFUL')
            ->and($array['crawledAs'])->toBe('MOBILE')
            ->and($array['googleCanonical'])->toBe('https://example.com/page')
            ->and($array['userCanonical'])->toBe('https://example.com/page')
            ->and($array['isMobileFriendly'])->toBeTrue()
            ->and($array['mobileUsabilityIssue'])->toBeNull()
            ->and($array['isIndexed'])->toBeTrue()
            ->and($array['isIndexable'])->toBeTrue()
            ->and($array['isCrawlable'])->toBeTrue();
    });
});
