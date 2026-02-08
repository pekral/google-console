<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\DTO;

use Pekral\GoogleConsole\Enum\IndexingChangeType;
use Pekral\GoogleConsole\Enum\IndexingCheckStatus;

final readonly class IndexingChange
{

    /**
     * @param list<\Pekral\GoogleConsole\Enum\IndexingCheckReasonCode> $currentReasonCodes
     */
    public function __construct(
        public string $url,
        public IndexingChangeType $changeType,
        public IndexingCheckStatus $previousStatus,
        public IndexingCheckStatus $currentStatus,
        public array $currentReasonCodes = [],
    ) {
    }

}
