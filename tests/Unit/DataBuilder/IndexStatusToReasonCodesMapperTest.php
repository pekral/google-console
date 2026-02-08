<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\DataBuilder\IndexStatusToReasonCodesDataBuilder;
use Pekral\GoogleConsole\Enum\IndexingCheckReasonCode;

describe(IndexStatusToReasonCodesDataBuilder::class, function (): void {

    $mapper = new IndexStatusToReasonCodesDataBuilder();

    it('returns empty list for empty index status', function () use ($mapper): void {
        $result = $mapper->map([]);

        expect($result)->toBe([]);
    });

    it('returns ROBOTS_BLOCKED when robots txt disallows', function () use ($mapper): void {
        $indexStatus = [
            'robotsTxtState' => 'DISALLOWED',
        ];

        $result = $mapper->map($indexStatus);

        expect($result)->toContain(IndexingCheckReasonCode::ROBOTS_BLOCKED);
    });

    it('returns no robots reason when robots allowed', function () use ($mapper): void {
        $indexStatus = [
            'robotsTxtState' => 'ALLOWED',
        ];

        $result = $mapper->map($indexStatus);

        expect($result)->not->toContain(IndexingCheckReasonCode::ROBOTS_BLOCKED);
    });

    it('returns META_NOINDEX for BLOCKED_BY_META_TAG', function () use ($mapper): void {
        $indexStatus = [
            'indexingState' => 'BLOCKED_BY_META_TAG',
        ];

        $result = $mapper->map($indexStatus);

        expect($result)->toContain(IndexingCheckReasonCode::META_NOINDEX);
    });

    it('returns META_NOINDEX for BLOCKED_BY_HTTP_HEADER', function () use ($mapper): void {
        $indexStatus = [
            'indexingState' => 'BLOCKED_BY_HTTP_HEADER',
        ];

        $result = $mapper->map($indexStatus);

        expect($result)->toContain(IndexingCheckReasonCode::META_NOINDEX);
    });

    it('returns SOFT_404_SUSPECTED for page fetch state SOFT_404', function () use ($mapper): void {
        $indexStatus = [
            'pageFetchState' => 'SOFT_404',
        ];

        $result = $mapper->map($indexStatus);

        expect($result)->toContain(IndexingCheckReasonCode::SOFT_404_SUSPECTED);
    });

    it('returns REDIRECTED for page fetch state REDIRECT_ERROR', function () use ($mapper): void {
        $indexStatus = [
            'pageFetchState' => 'REDIRECT_ERROR',
        ];

        $result = $mapper->map($indexStatus);

        expect($result)->toContain(IndexingCheckReasonCode::REDIRECTED);
    });

    it('returns AUTH_REQUIRED_OR_FAILED for page fetch state ACCESS_DENIED', function () use ($mapper): void {
        $indexStatus = [
            'pageFetchState' => 'ACCESS_DENIED',
        ];

        $result = $mapper->map($indexStatus);

        expect($result)->toContain(IndexingCheckReasonCode::AUTH_REQUIRED_OR_FAILED);
    });

    it('returns TIMEOUT for page fetch state INTERNAL_CRAWL_ERROR', function () use ($mapper): void {
        $indexStatus = [
            'pageFetchState' => 'INTERNAL_CRAWL_ERROR',
        ];

        $result = $mapper->map($indexStatus);

        expect($result)->toContain(IndexingCheckReasonCode::TIMEOUT);
    });

    it('returns HTTP_STATUS_NOT_200 for page fetch state BLOCKED_4XX', function () use ($mapper): void {
        $indexStatus = [
            'pageFetchState' => 'BLOCKED_4XX',
        ];

        $result = $mapper->map($indexStatus);

        expect($result)->toContain(IndexingCheckReasonCode::HTTP_STATUS_NOT_200);
    });

    it('returns INDEXING_PENDING when coverage state is Crawled - not indexed', function () use ($mapper): void {
        $indexStatus = [
            'coverageState' => 'Crawled - not indexed',
        ];

        $result = $mapper->map($indexStatus);

        expect($result)->toContain(IndexingCheckReasonCode::INDEXING_PENDING);
    });

    it('returns DUPLICATE_WITHOUT_CANONICAL when coverage state contains duplicate without user-selected canonical', function () use ($mapper): void {
        $indexStatus = [
            'coverageState' => 'Duplicate without user-selected canonical',
        ];

        $result = $mapper->map($indexStatus);

        expect($result)->toContain(IndexingCheckReasonCode::DUPLICATE_WITHOUT_CANONICAL);
    });

    it('returns DUPLICATE_CANONICAL_OTHER when coverage state contains duplicate chose different', function () use ($mapper): void {
        $indexStatus = [
            'coverageState' => 'Duplicate, Google chose different canonical',
        ];

        $result = $mapper->map($indexStatus);

        expect($result)->toContain(IndexingCheckReasonCode::DUPLICATE_CANONICAL_OTHER);
    });

    it('returns SOFT_404_SUSPECTED when coverage state contains Soft 404', function () use ($mapper): void {
        $indexStatus = [
            'coverageState' => 'Soft 404',
        ];

        $result = $mapper->map($indexStatus);

        expect($result)->toContain(IndexingCheckReasonCode::SOFT_404_SUSPECTED);
    });

    it('returns CANONICAL_MISMATCH when google and user canonical differ', function () use ($mapper): void {
        $indexStatus = [
            'googleCanonical' => 'https://example.com/page-a',
            'userCanonical' => 'https://example.com/page-b',
        ];

        $result = $mapper->map($indexStatus);

        expect($result)->toContain(IndexingCheckReasonCode::CANONICAL_MISMATCH);
    });

    it('returns no canonical reason when both canonicals match', function () use ($mapper): void {
        $indexStatus = [
            'googleCanonical' => 'https://example.com/page',
            'userCanonical' => 'https://example.com/page',
        ];

        $result = $mapper->map($indexStatus);

        expect($result)->not->toContain(IndexingCheckReasonCode::CANONICAL_MISMATCH);
    });

    it('returns no canonical reason when one canonical is empty', function () use ($mapper): void {
        $indexStatus = [
            'googleCanonical' => 'https://example.com/page',
            'userCanonical' => '',
        ];

        $result = $mapper->map($indexStatus);

        expect($result)->not->toContain(IndexingCheckReasonCode::CANONICAL_MISMATCH);
    });

    it('returns no page fetch reason for SUCCESSFUL', function () use ($mapper): void {
        $indexStatus = [
            'pageFetchState' => 'SUCCESSFUL',
        ];

        $result = $mapper->map($indexStatus);

        expect($result)->toBe([]);
    });

    it('returns unique reason codes when multiple conditions match', function () use ($mapper): void {
        $indexStatus = [
            'robotsTxtState' => 'DISALLOWED',
            'indexingState' => 'BLOCKED_BY_META_TAG',
            'pageFetchState' => 'SOFT_404',
        ];

        $result = $mapper->map($indexStatus);

        expect($result)->toContain(IndexingCheckReasonCode::ROBOTS_BLOCKED)
            ->and($result)->toContain(IndexingCheckReasonCode::META_NOINDEX)
            ->and($result)->toContain(IndexingCheckReasonCode::SOFT_404_SUSPECTED);
    });

    it('deduplicates reason codes when same code from multiple sources', function () use ($mapper): void {
        $indexStatus = [
            'robotsTxtState' => 'DISALLOWED',
            'pageFetchState' => 'BLOCKED_ROBOTS_TXT',
        ];

        $result = $mapper->map($indexStatus);

        $robotsBlockedCount = count(array_filter($result, static fn (IndexingCheckReasonCode $c): bool => $c === IndexingCheckReasonCode::ROBOTS_BLOCKED));
        expect($robotsBlockedCount)->toBe(1)
            ->and($result)->toContain(IndexingCheckReasonCode::ROBOTS_BLOCKED);
    });

    it('returns no page fetch reason for PAGE_FETCH_STATE_UNSPECIFIED', function () use ($mapper): void {
        $indexStatus = [
            'pageFetchState' => 'PAGE_FETCH_STATE_UNSPECIFIED',
        ];

        $result = $mapper->map($indexStatus);

        expect($result)->toBe([]);
    });
});
