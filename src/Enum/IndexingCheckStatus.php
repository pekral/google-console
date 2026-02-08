<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\Enum;

use Pekral\GoogleConsole\DTO\UrlInspectionResult;

enum IndexingCheckStatus: string
{

    case INDEXED = 'INDEXED';

    case NOT_INDEXED = 'NOT_INDEXED';

    case UNKNOWN = 'UNKNOWN';

    private const string VERDICT_PASS = 'PASS';

    private const string VERDICT_FAIL = 'FAIL';

    public static function fromUrlInspectionResult(UrlInspectionResult $result): self
    {
        if ($result->indexingCheckResult !== null) {
            return $result->indexingCheckResult->primaryStatus;
        }

        return self::fromVerdict($result->verdict);
    }

    private static function fromVerdict(string $verdict): self
    {
        return match ($verdict) {
            self::VERDICT_PASS => self::INDEXED,
            self::VERDICT_FAIL => self::NOT_INDEXED,
            default => self::UNKNOWN,
        };
    }

}
