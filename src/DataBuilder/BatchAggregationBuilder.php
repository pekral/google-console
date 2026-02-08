<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\DataBuilder;

use Pekral\GoogleConsole\DTO\BatchAggregation;
use Pekral\GoogleConsole\DTO\PerUrlInspectionResult;
use Pekral\GoogleConsole\Enum\IndexingCheckStatus;

final readonly class BatchAggregationBuilder
{

    /**
     * @param array<int, \Pekral\GoogleConsole\DTO\PerUrlInspectionResult> $perUrlResults
     */
    public function build(array $perUrlResults): BatchAggregation
    {
        $indexedCount = 0;
        $notIndexedCount = 0;
        $unknownCount = 0;
        $reasonCodeCounts = [];

        foreach ($perUrlResults as $item) {
            if ($item->status === IndexingCheckStatus::INDEXED) {
                $indexedCount++;
            } elseif ($item->status === IndexingCheckStatus::NOT_INDEXED) {
                $notIndexedCount++;
            } else {
                $unknownCount++;
            }

            $reasonCodeCounts = $this->mergeReasonCodes($item, $reasonCodeCounts);
        }

        return new BatchAggregation(
            indexedCount: $indexedCount,
            notIndexedCount: $notIndexedCount,
            unknownCount: $unknownCount,
            reasonCodeCounts: $reasonCodeCounts,
        );
    }

    /**
     * @param array<string, int> $reasonCodeCounts
     * @return array<string, int>
     */
    private function mergeReasonCodes(PerUrlInspectionResult $item, array $reasonCodeCounts): array
    {
        $result = $item->result;

        if ($result->indexingCheckResult === null) {
            return $reasonCodeCounts;
        }

        foreach ($result->indexingCheckResult->reasonCodes as $code) {
            $key = $code->value;
            $reasonCodeCounts[$key] = ($reasonCodeCounts[$key] ?? 0) + 1;
        }

        return $reasonCodeCounts;
    }

}
