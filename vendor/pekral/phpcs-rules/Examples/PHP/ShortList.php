<?php

declare(strict_types = 1);

namespace Example\PHP;

final class ShortList
{

    public function foo(): int
    {
        [$a, $b] = [1, 2];

        return $a + $b;
    }

} 