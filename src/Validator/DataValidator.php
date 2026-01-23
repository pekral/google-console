<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\Validator;

use Pekral\GoogleConsole\Enum\Dimension;

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

}
