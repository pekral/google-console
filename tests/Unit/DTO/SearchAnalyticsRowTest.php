<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\DTO\SearchAnalyticsRow;

describe(SearchAnalyticsRow::class, function (): void {

    it('creates row from api response with all data', function (): void {
        $data = [
            'keys' => ['test query', 'https://example.com/page'],
            'clicks' => 100.0,
            'impressions' => 1_000.0,
            'ctr' => 0.1,
            'position' => 5.5,
        ];

        $row = SearchAnalyticsRow::fromApiResponse($data, ['query', 'page']);

        expect($row->keys)->toBe(['query' => 'test query', 'page' => 'https://example.com/page'])
            ->and($row->clicks)->toBe(100.0)
            ->and($row->impressions)->toBe(1_000.0)
            ->and($row->ctr)->toBe(0.1)
            ->and($row->position)->toBe(5.5);
    });

    it('creates row with default values for missing data', function (): void {
        $row = SearchAnalyticsRow::fromApiResponse([], ['query']);

        expect($row->keys)->toBe(['query' => ''])
            ->and($row->clicks)->toBe(0.0)
            ->and($row->impressions)->toBe(0.0)
            ->and($row->ctr)->toBe(0.0)
            ->and($row->position)->toBe(0.0);
    });

    it('returns key value by dimension name', function (): void {
        $data = [
            'keys' => ['test query', 'https://example.com/page'],
            'clicks' => 50.0,
            'impressions' => 500.0,
            'ctr' => 0.1,
            'position' => 3.0,
        ];

        $row = SearchAnalyticsRow::fromApiResponse($data, ['query', 'page']);

        expect($row->getKey('query'))->toBe('test query')
            ->and($row->getKey('page'))->toBe('https://example.com/page')
            ->and($row->getKey('nonexistent'))->toBeNull();
    });

    it('returns query value via helper method', function (): void {
        $data = [
            'keys' => ['my search query'],
            'clicks' => 10.0,
            'impressions' => 100.0,
            'ctr' => 0.1,
            'position' => 2.0,
        ];

        $row = SearchAnalyticsRow::fromApiResponse($data, ['query']);

        expect($row->getQuery())->toBe('my search query');
    });

    it('returns null for query when not in dimensions', function (): void {
        $data = [
            'keys' => ['https://example.com/page'],
            'clicks' => 10.0,
            'impressions' => 100.0,
            'ctr' => 0.1,
            'position' => 2.0,
        ];

        $row = SearchAnalyticsRow::fromApiResponse($data, ['page']);

        expect($row->getQuery())->toBeNull();
    });

    it('returns page value via helper method', function (): void {
        $data = [
            'keys' => ['https://example.com/page'],
            'clicks' => 10.0,
            'impressions' => 100.0,
            'ctr' => 0.1,
            'position' => 2.0,
        ];

        $row = SearchAnalyticsRow::fromApiResponse($data, ['page']);

        expect($row->getPage())->toBe('https://example.com/page');
    });

    it('returns country value via helper method', function (): void {
        $data = [
            'keys' => ['CZE'],
            'clicks' => 10.0,
            'impressions' => 100.0,
            'ctr' => 0.1,
            'position' => 2.0,
        ];

        $row = SearchAnalyticsRow::fromApiResponse($data, ['country']);

        expect($row->getCountry())->toBe('CZE');
    });

    it('returns device value via helper method', function (): void {
        $data = [
            'keys' => ['MOBILE'],
            'clicks' => 10.0,
            'impressions' => 100.0,
            'ctr' => 0.1,
            'position' => 2.0,
        ];

        $row = SearchAnalyticsRow::fromApiResponse($data, ['device']);

        expect($row->getDevice())->toBe('MOBILE');
    });

    it('handles multiple dimensions correctly', function (): void {
        $data = [
            'keys' => ['test query', 'https://example.com/', 'USA', 'DESKTOP'],
            'clicks' => 200.0,
            'impressions' => 2_000.0,
            'ctr' => 0.1,
            'position' => 1.5,
        ];

        $row = SearchAnalyticsRow::fromApiResponse($data, ['query', 'page', 'country', 'device']);

        expect($row->getQuery())->toBe('test query')
            ->and($row->getPage())->toBe('https://example.com/')
            ->and($row->getCountry())->toBe('USA')
            ->and($row->getDevice())->toBe('DESKTOP');
    });

    it('converts to array with all properties', function (): void {
        $data = [
            'keys' => ['test query'],
            'clicks' => 100.0,
            'impressions' => 1_000.0,
            'ctr' => 0.1,
            'position' => 5.5,
        ];

        $row = SearchAnalyticsRow::fromApiResponse($data, ['query']);
        $array = $row->toArray();

        expect($array['keys'])->toBe(['query' => 'test query'])
            ->and($array['clicks'])->toBe(100.0)
            ->and($array['impressions'])->toBe(1_000.0)
            ->and($array['ctr'])->toBe(0.1)
            ->and($array['position'])->toBe(5.5);
    });
});
