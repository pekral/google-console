<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\DTO;

use Pekral\GoogleConsole\Enum\BatchVerdict;

final readonly class BatchUrlInspectionResult
{

    /**
     * @param array<string, \Pekral\GoogleConsole\DTO\PerUrlInspectionResult> $perUrlResults
     * @param array<int, \Pekral\GoogleConsole\DTO\PerUrlInspectionResult> $criticalUrlResults
     */
    public function __construct(
        public array $perUrlResults,
        public BatchAggregation $aggregation,
        public array $criticalUrlResults,
        public BatchVerdict $batchVerdict,
    ) {
    }

}
