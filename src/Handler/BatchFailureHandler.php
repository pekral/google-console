<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\Handler;

use Pekral\GoogleConsole\DTO\PerUrlInspectionResult;
use Pekral\GoogleConsole\DTO\UrlInspectionResult;
use Pekral\GoogleConsole\Enum\FailureType;
use Pekral\GoogleConsole\Enum\IndexingCheckReasonCode;
use Pekral\GoogleConsole\Enum\IndexingCheckStatus;
use Pekral\GoogleConsole\Exception\GoogleConsoleFailure;

final readonly class BatchFailureHandler
{

    private const array SOFT_FAILURE_HTTP_CODES = [408, 429, 500, 502, 503, 504];

    public function isSoftFailure(GoogleConsoleFailure $exception): bool
    {
        return in_array($exception->getCode(), self::SOFT_FAILURE_HTTP_CODES, true);
    }

    public function buildSoftFailureResult(string $url, GoogleConsoleFailure $exception): PerUrlInspectionResult
    {
        $reasonCode = $this->mapFailureToReasonCode($exception);

        return new PerUrlInspectionResult(
            url: $url,
            status: IndexingCheckStatus::UNKNOWN,
            result: UrlInspectionResult::forSoftFailure($reasonCode),
            failureType: FailureType::SOFT,
        );
    }

    private function mapFailureToReasonCode(GoogleConsoleFailure $exception): IndexingCheckReasonCode
    {
        return match ($exception->getCode()) {
            429 => IndexingCheckReasonCode::RATE_LIMITED,
            408, 504 => IndexingCheckReasonCode::TIMEOUT,
            default => IndexingCheckReasonCode::INSUFFICIENT_DATA,
        };
    }

}
