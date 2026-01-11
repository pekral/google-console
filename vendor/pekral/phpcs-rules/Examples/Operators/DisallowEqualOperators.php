<?php

declare(strict_types = 1);

namespace Example\Operators;

final class DisallowEqualOperators
{

    public function foo(int $a): bool
    {
        return $a === 1;
    }

} 