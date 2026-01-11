<?php

declare(strict_types = 1);

namespace Example\ControlStructures;

final class RequireShortTernaryOperator
{

    public function foo(?int $a): int
    {
        return $a ?: 0;
    }

} 