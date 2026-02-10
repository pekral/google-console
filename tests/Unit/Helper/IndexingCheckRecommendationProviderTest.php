<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\Enum\IndexingCheckReasonCode;
use Pekral\GoogleConsole\Helper\IndexingCheckRecommendationProvider;

describe(IndexingCheckRecommendationProvider::class, function (): void {

    it('returns empty list for empty reason codes', function (): void {
        expect(IndexingCheckRecommendationProvider::getRecommendations([]))->toBe([]);
    });

    it('returns empty list for INDEXED_CONFIRMED which has no recommendation', function (): void {
        expect(IndexingCheckRecommendationProvider::getRecommendations([IndexingCheckReasonCode::INDEXED_CONFIRMED]))->toBe([]);
    });

    it('returns recommendation for META_NOINDEX', function (): void {
        $recommendations = IndexingCheckRecommendationProvider::getRecommendations([IndexingCheckReasonCode::META_NOINDEX]);

        expect($recommendations)->toHaveCount(1)
            ->and($recommendations[0])->toContain('meta noindex');
    });

    it('returns recommendation for ROBOTS_BLOCKED', function (): void {
        $recommendations = IndexingCheckRecommendationProvider::getRecommendations([IndexingCheckReasonCode::ROBOTS_BLOCKED]);

        expect($recommendations)->toHaveCount(1)
            ->and($recommendations[0])->toContain('robots.txt');
    });

    it('returns recommendation for INSUFFICIENT_DATA', function (): void {
        $recommendations = IndexingCheckRecommendationProvider::getRecommendations([IndexingCheckReasonCode::INSUFFICIENT_DATA]);

        expect($recommendations)->toHaveCount(1)
            ->and($recommendations[0])->toContain('inspection');
    });

    it('deduplicates same recommendation when multiple codes map to same text', function (): void {
        $recommendations = IndexingCheckRecommendationProvider::getRecommendations([
            IndexingCheckReasonCode::NOT_INDEXED_CONFIRMED,
            IndexingCheckReasonCode::META_NOINDEX,
        ]);

        expect($recommendations)->toHaveCount(2);
    });

    it('returns multiple recommendations for multiple actionable reason codes', function (): void {
        $recommendations = IndexingCheckRecommendationProvider::getRecommendations([
            IndexingCheckReasonCode::ROBOTS_BLOCKED,
            IndexingCheckReasonCode::HTTP_STATUS_NOT_200,
        ]);

        expect($recommendations)->toHaveCount(2);
    });
});
