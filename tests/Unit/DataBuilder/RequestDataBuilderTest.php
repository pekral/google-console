<?php

declare(strict_types = 1);

use Google\Service\Webmasters\SearchAnalyticsQueryRequest;
use Pekral\GoogleConsole\DataBuilder\RequestDataBuilder;

describe(RequestDataBuilder::class, function (): void {

    it('builds search analytics request with all parameters', function (): void {
        $builder = new RequestDataBuilder();
        $startDate = new DateTimeImmutable('2024-01-01');
        $endDate = new DateTimeImmutable('2024-01-31');
        $dimensions = ['query', 'page'];
        $rowLimit = 500;
        $startRow = 10;

        $request = $builder->buildSearchAnalyticsRequest(
            $startDate,
            $endDate,
            $dimensions,
            $rowLimit,
            $startRow,
        );

        expect($request)->toBeInstanceOf(SearchAnalyticsQueryRequest::class)
            ->and($request->getStartDate())->toBe('2024-01-01')
            ->and($request->getEndDate())->toBe('2024-01-31')
            ->and($request->getDimensions())->toBe(['query', 'page'])
            ->and($request->getRowLimit())->toBe(500)
            ->and($request->getStartRow())->toBe(10);
    });

    it('builds search analytics request with default-like values', function (): void {
        $builder = new RequestDataBuilder();
        $startDate = new DateTimeImmutable('2024-06-01');
        $endDate = new DateTimeImmutable('2024-06-30');

        $request = $builder->buildSearchAnalyticsRequest(
            $startDate,
            $endDate,
            ['query'],
            1000,
            0,
        );

        expect($request)->toBeInstanceOf(SearchAnalyticsQueryRequest::class)
            ->and($request->getStartDate())->toBe('2024-06-01')
            ->and($request->getEndDate())->toBe('2024-06-30')
            ->and($request->getDimensions())->toBe(['query'])
            ->and($request->getRowLimit())->toBe(1000)
            ->and($request->getStartRow())->toBe(0);
    });

    it('builds request with empty dimensions array', function (): void {
        $builder = new RequestDataBuilder();

        $request = $builder->buildSearchAnalyticsRequest(
            new DateTimeImmutable('2024-01-01'),
            new DateTimeImmutable('2024-01-31'),
            [],
            100,
            0,
        );

        expect($request->getDimensions())->toBe([]);
    });
});
