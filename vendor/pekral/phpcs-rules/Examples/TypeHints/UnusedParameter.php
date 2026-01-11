<?php

declare(strict_types = 1);

namespace Example\TypeHints;

final class UnusedParameter
{

    public function foo(int $a): int
    {
        return $a + 1;
    }

} 