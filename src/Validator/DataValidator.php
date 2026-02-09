<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\Validator;

use Pekral\GoogleConsole\Enum\Dimension;
use Pekral\GoogleConsole\Exception\BatchSizeLimitExceeded;

final class DataValidator
{

    /**
     * @param array<string> $dimensions
     * @throws \Pekral\GoogleConsole\Exception\GoogleConsoleFailure
     */
    public function validateDimensions(array $dimensions): void
    {
        Dimension::fromArray($dimensions);
    }

    /**
     * @throws \Pekral\GoogleConsole\Exception\BatchSizeLimitExceeded
     */
    public function validateBatchSize(int $urlCount, int $maxBatchSize): void
    {
        if ($urlCount > $maxBatchSize) {
            throw new BatchSizeLimitExceeded($urlCount, $maxBatchSize);
        }
    }

}
