<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\Exception;

final class BatchSizeLimitExceeded extends GoogleConsoleFailure
{

    public function __construct(int $urlCount, int $maxBatchSize)
    {
        parent::__construct(
            sprintf('Batch size %d exceeds maximum of %d URLs', $urlCount, $maxBatchSize),
        );
    }

}
