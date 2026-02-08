<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole;

use Pekral\GoogleConsole\DataBuilder\BatchAggregationBuilder;
use Pekral\GoogleConsole\DTO\IndexingChange;
use Pekral\GoogleConsole\DTO\IndexingComparisonResult;
use Pekral\GoogleConsole\DTO\PerUrlInspectionResult;
use Pekral\GoogleConsole\Enum\IndexingChangeType;
use Pekral\GoogleConsole\Enum\IndexingCheckStatus;

final readonly class IndexingRunComparator
{

    public function __construct(private BatchAggregationBuilder $batchAggregationBuilder = new BatchAggregationBuilder()) {
    }

    /**
     * Compares two indexing runs (previous vs current). Only URLs present in both runs are compared.
     *
     * @param array<string, \Pekral\GoogleConsole\DTO\PerUrlInspectionResult> $previous
     * @param array<string, \Pekral\GoogleConsole\DTO\PerUrlInspectionResult> $current
     */
    public function compare(array $previous, array $current): IndexingComparisonResult
    {
        $overlappingUrls = array_keys(array_intersect_key($previous, $current));

        if ($overlappingUrls === []) {
            return new IndexingComparisonResult(
                changes: [],
                indexedDelta: 0,
                notIndexedDelta: 0,
                unknownDelta: 0,
                dominantReasonCodes: [],
            );
        }

        $changes = $this->buildChanges($previous, $current, $overlappingUrls);
        $deltas = $this->computeDeltas($previous, $current, $overlappingUrls);
        $currentOverlapping = array_intersect_key($current, array_flip($overlappingUrls));
        $dominantReasonCodes = $this->buildDominantReasonCodes(array_values($currentOverlapping));

        return new IndexingComparisonResult(
            changes: $changes,
            indexedDelta: $deltas['indexed'],
            notIndexedDelta: $deltas['notIndexed'],
            unknownDelta: $deltas['unknown'],
            dominantReasonCodes: $dominantReasonCodes,
        );
    }

    /**
     * @param array<string, \Pekral\GoogleConsole\DTO\PerUrlInspectionResult> $previous
     * @param array<string, \Pekral\GoogleConsole\DTO\PerUrlInspectionResult> $current
     * @param list<string> $urls
     * @return list<\Pekral\GoogleConsole\DTO\IndexingChange>
     */
    private function buildChanges(array $previous, array $current, array $urls): array
    {
        $list = [];

        foreach ($urls as $url) {
            $prev = $previous[$url];
            $curr = $current[$url];
            $changeType = IndexingChangeType::between($prev->status, $curr->status);

            if ($changeType === null) {
                continue;
            }

            $list[] = new IndexingChange(
                url: $url,
                changeType: $changeType,
                previousStatus: $prev->status,
                currentStatus: $curr->status,
                currentReasonCodes: $this->extractReasonCodes($curr),
            );
        }

        return $list;
    }

    /**
     * @return list<\Pekral\GoogleConsole\Enum\IndexingCheckReasonCode>
     */
    private function extractReasonCodes(PerUrlInspectionResult $result): array
    {
        $check = $result->result->indexingCheckResult;

        if ($check === null) {
            return [];
        }

        return $check->reasonCodes;
    }

    /**
     * @param array<string, \Pekral\GoogleConsole\DTO\PerUrlInspectionResult> $previous
     * @param array<string, \Pekral\GoogleConsole\DTO\PerUrlInspectionResult> $current
     * @param list<string> $urls
     * @return array{indexed: int, notIndexed: int, unknown: int}
     */
    private function computeDeltas(array $previous, array $current, array $urls): array
    {
        $prevCounts = $this->countByStatus($previous, $urls);
        $currCounts = $this->countByStatus($current, $urls);

        return [
            'indexed' => $currCounts['indexed'] - $prevCounts['indexed'],
            'notIndexed' => $currCounts['notIndexed'] - $prevCounts['notIndexed'],
            'unknown' => $currCounts['unknown'] - $prevCounts['unknown'],
        ];
    }

    /**
     * @param array<string, \Pekral\GoogleConsole\DTO\PerUrlInspectionResult> $results
     * @param list<string> $urls
     * @return array{indexed: int, notIndexed: int, unknown: int}
     */
    private function countByStatus(array $results, array $urls): array
    {
        $indexed = 0;
        $notIndexed = 0;
        $unknown = 0;

        foreach ($urls as $url) {
            if ($results[$url]->status === IndexingCheckStatus::INDEXED) {
                $indexed++;
            } elseif ($results[$url]->status === IndexingCheckStatus::NOT_INDEXED) {
                $notIndexed++;
            } else {
                $unknown++;
            }
        }

        return ['indexed' => $indexed, 'notIndexed' => $notIndexed, 'unknown' => $unknown];
    }

    /**
     * @param array<int, \Pekral\GoogleConsole\DTO\PerUrlInspectionResult> $currentResults
     * @return array<string, int>
     */
    private function buildDominantReasonCodes(array $currentResults): array
    {
        $aggregation = $this->batchAggregationBuilder->build($currentResults);

        $codes = $aggregation->reasonCodeCounts;
        arsort($codes, SORT_NUMERIC);

        return $codes;
    }

}
