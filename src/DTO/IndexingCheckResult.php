<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\DTO;

use DateTimeImmutable;
use Pekral\GoogleConsole\Enum\IndexingCheckConfidence;
use Pekral\GoogleConsole\Enum\IndexingCheckReasonCode;
use Pekral\GoogleConsole\Enum\IndexingCheckSourceType;
use Pekral\GoogleConsole\Enum\IndexingCheckStatus;

final readonly class IndexingCheckResult
{

    /**
     * @param list<\Pekral\GoogleConsole\Enum\IndexingCheckReasonCode> $reasonCodes
     */
    public function __construct(
        public IndexingCheckStatus $primaryStatus,
        public IndexingCheckConfidence $confidence,
        public array $reasonCodes,
        public DateTimeImmutable $checkedAt,
        public IndexingCheckSourceType $sourceType,
    ) {
    }

    /**
     * @return array{
     *     primaryStatus: string,
     *     confidence: string,
     *     reason_codes: list<string>,
     *     checked_at: string,
     *     source_type: string
     * }
     */
    public function toArray(): array
    {
        return [
            'checked_at' => $this->checkedAt->format('c'),
            'confidence' => $this->confidence->value,
            'primaryStatus' => $this->primaryStatus->value,
            'reason_codes' => array_map(static fn (IndexingCheckReasonCode $code): string => $code->value, $this->reasonCodes),
            'source_type' => $this->sourceType->value,
        ];
    }

}
