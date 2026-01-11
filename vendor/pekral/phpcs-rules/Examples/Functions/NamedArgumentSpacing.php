<?php

declare(strict_types = 1);

namespace Example\Functions;

function sum(int $a, int $b): int
{
    return $a + $b;
}

final class NamedArgumentSpacing
{

    public function foo(): int
    {
        // Named arguments with correct spacing
        return sum(a: 1, b: 2);
    }

} 