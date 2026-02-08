<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\DTO;

final readonly class BatchAggregation
{

    /**
     * @param array<string, int> $reasonCodeCounts
     */
    public function __construct(public int $indexedCount, public int $notIndexedCount, public int $unknownCount, public array $reasonCodeCounts) {
    }

}
