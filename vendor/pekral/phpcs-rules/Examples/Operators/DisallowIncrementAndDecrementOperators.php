<?php

declare(strict_types = 1);

namespace Example\Operators;

final class DisallowIncrementAndDecrementOperators
{

    public function foo(): int
    {
        $a = 1;
        $a += 1;

        return $a;
    }

} 