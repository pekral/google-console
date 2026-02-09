<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\DTO\PerUrlInspectionResult;
use Pekral\GoogleConsole\DTO\UrlInspectionResult;
use Pekral\GoogleConsole\Enum\FailureType;
use Pekral\GoogleConsole\Enum\IndexingCheckReasonCode;
use Pekral\GoogleConsole\Enum\IndexingCheckStatus;

describe(PerUrlInspectionResult::class, function (): void {

    it('creates result without failure type', function (): void {
        $result = UrlInspectionResult::fromApiResponse([
            'indexStatusResult' => ['verdict' => 'PASS'],
        ]);

        $perUrl = new PerUrlInspectionResult(
            url: 'https://example.com/page',
            status: IndexingCheckStatus::INDEXED,
            result: $result,
        );

        expect($perUrl->url)->toBe('https://example.com/page')
            ->and($perUrl->status)->toBe(IndexingCheckStatus::INDEXED)
            ->and($perUrl->failureType)->toBeNull()
            ->and($perUrl->isSoftFailure())->toBeFalse();
    });

    it('creates result with soft failure type', function (): void {
        $result = UrlInspectionResult::forSoftFailure(IndexingCheckReasonCode::RATE_LIMITED);

        $perUrl = new PerUrlInspectionResult(
            url: 'https://example.com/rate-limited',
            status: IndexingCheckStatus::UNKNOWN,
            result: $result,
            failureType: FailureType::SOFT,
        );

        expect($perUrl->failureType)->toBe(FailureType::SOFT)
            ->and($perUrl->isSoftFailure())->toBeTrue();
    });

    it('creates result with hard failure type', function (): void {
        $result = UrlInspectionResult::fromApiResponse([]);

        $perUrl = new PerUrlInspectionResult(
            url: 'https://example.com/error',
            status: IndexingCheckStatus::UNKNOWN,
            result: $result,
            failureType: FailureType::HARD,
        );

        expect($perUrl->failureType)->toBe(FailureType::HARD)
            ->and($perUrl->isSoftFailure())->toBeFalse();
    });
});
