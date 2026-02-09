<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\Enum\FailureType;
use Pekral\GoogleConsole\Enum\IndexingCheckReasonCode;
use Pekral\GoogleConsole\Enum\IndexingCheckStatus;
use Pekral\GoogleConsole\Exception\GoogleConsoleFailure;
use Pekral\GoogleConsole\Handler\BatchFailureHandler;

describe(BatchFailureHandler::class, function (): void {

    it('classifies rate limit as soft failure', function (): void {
        $handler = new BatchFailureHandler();
        $exception = new GoogleConsoleFailure('Quota exceeded', 429);

        expect($handler->isSoftFailure($exception))->toBeTrue();
    });

    it('classifies timeout as soft failure', function (int $code): void {
        $handler = new BatchFailureHandler();
        $exception = new GoogleConsoleFailure('Timeout', $code);

        expect($handler->isSoftFailure($exception))->toBeTrue();
    })->with([408, 504]);

    it('classifies server errors as soft failure', function (int $code): void {
        $handler = new BatchFailureHandler();
        $exception = new GoogleConsoleFailure('Server error', $code);

        expect($handler->isSoftFailure($exception))->toBeTrue();
    })->with([500, 502, 503]);

    it('classifies client errors as hard failure', function (int $code): void {
        $handler = new BatchFailureHandler();
        $exception = new GoogleConsoleFailure('Client error', $code);

        expect($handler->isSoftFailure($exception))->toBeFalse();
    })->with([400, 401, 403, 404, 422]);

    it('classifies zero code as hard failure', function (): void {
        $handler = new BatchFailureHandler();
        $exception = new GoogleConsoleFailure('Unknown error');

        expect($handler->isSoftFailure($exception))->toBeFalse();
    });

    it('builds soft failure result with rate limited reason code', function (): void {
        $handler = new BatchFailureHandler();
        $exception = new GoogleConsoleFailure('Quota exceeded', 429);

        $result = $handler->buildSoftFailureResult('https://example.com/rate-limited', $exception);

        expect($result->url)->toBe('https://example.com/rate-limited')
            ->and($result->status)->toBe(IndexingCheckStatus::UNKNOWN)
            ->and($result->failureType)->toBe(FailureType::SOFT)
            ->and($result->isSoftFailure())->toBeTrue()
            ->and($result->result->indexingCheckResult?->reasonCodes[0])->toBe(IndexingCheckReasonCode::RATE_LIMITED);
    });

    it('builds soft failure result with timeout reason code', function (): void {
        $handler = new BatchFailureHandler();
        $exception = new GoogleConsoleFailure('Timeout', 504);

        $result = $handler->buildSoftFailureResult('https://example.com/timeout', $exception);

        expect($result->result->indexingCheckResult?->reasonCodes[0])->toBe(IndexingCheckReasonCode::TIMEOUT);
    });

    it('builds soft failure result with insufficient data for server error', function (): void {
        $handler = new BatchFailureHandler();
        $exception = new GoogleConsoleFailure('Internal server error', 500);

        $result = $handler->buildSoftFailureResult('https://example.com/error', $exception);

        expect($result->result->indexingCheckResult?->reasonCodes[0])->toBe(IndexingCheckReasonCode::INSUFFICIENT_DATA);
    });

    it('builds soft failure result with 408 timeout reason code', function (): void {
        $handler = new BatchFailureHandler();
        $exception = new GoogleConsoleFailure('Request timeout', 408);

        $result = $handler->buildSoftFailureResult('https://example.com/timeout', $exception);

        expect($result->result->indexingCheckResult?->reasonCodes[0])->toBe(IndexingCheckReasonCode::TIMEOUT);
    });
});
