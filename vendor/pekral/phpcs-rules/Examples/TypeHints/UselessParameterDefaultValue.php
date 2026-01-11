<?php

declare(strict_types = 1);

namespace Example\TypeHints;

final class UselessParameterDefaultValue
{

    public function foo(int $a): int
    {
        return $a;
    }

} 