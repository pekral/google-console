<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\DTO;

final readonly class IndexingComparisonResult
{

    /**
     * @param list<\Pekral\GoogleConsole\DTO\IndexingChange> $changes
     * @param array<string, int> $dominantReasonCodes reason code value => count from current run (overlapping URLs)
     */
    public function __construct(
        public array $changes,
        public int $indexedDelta,
        public int $notIndexedDelta,
        public int $unknownDelta,
        public array $dominantReasonCodes = [],
    ) {
    }

}
