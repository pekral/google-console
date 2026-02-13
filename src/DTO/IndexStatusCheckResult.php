<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\DTO;

use DateTimeImmutable;
use Pekral\GoogleConsole\Enum\IndexingCheckConfidence;
use Pekral\GoogleConsole\Enum\IndexingCheckReasonCode;
use Pekral\GoogleConsole\Enum\IndexingCheckSourceType;
use Pekral\GoogleConsole\Enum\IndexingCheckStatus;

/**
 * Business DTO for the Index Status Checker: inspected URL plus status and reason codes.
 * Use checkIndexStatus() for this result; use inspectUrl() when you need full inspection (mobile, canonicals, etc.).
 */
final readonly class IndexStatusCheckResult
{

    /**
     * @param list<\Pekral\GoogleConsole\Enum\IndexingCheckReasonCode> $reasonCodes
     */
    public function __construct(
        public string $url,
        public IndexingCheckStatus $status,
        public array $reasonCodes,
        public IndexingCheckConfidence $confidence,
        public DateTimeImmutable $checkedAt,
        public IndexingCheckSourceType $sourceType,
    ) {
    }

    public static function fromIndexingCheckResult(string $url, IndexingCheckResult $check): self
    {
        return new self(
            url: $url,
            status: $check->primaryStatus,
            reasonCodes: $check->reasonCodes,
            confidence: $check->confidence,
            checkedAt: $check->checkedAt,
            sourceType: $check->sourceType,
        );
    }

    /**
     * @return array{
     *     url: string,
     *     status: string,
     *     reason_codes: list<string>,
     *     confidence: string,
     *     checked_at: string,
     *     source_type: string
     * }
     */
    public function toArray(): array
    {
        return [
            'checked_at' => $this->checkedAt->format('c'),
            'confidence' => $this->confidence->value,
            'reason_codes' => array_map(static fn (IndexingCheckReasonCode $code): string => $code->value, $this->reasonCodes),
            'source_type' => $this->sourceType->value,
            'status' => $this->status->value,
            'url' => $this->url,
        ];
    }

}
