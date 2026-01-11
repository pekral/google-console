<?php

declare(strict_types = 1);

namespace Example\Operators;

final class SpreadOperatorSpacing
{

    public function foo(): array
    {
        $a = [1, 2];
        $b = [3, 4];

        return [...$a, ...$b];
    }

} 