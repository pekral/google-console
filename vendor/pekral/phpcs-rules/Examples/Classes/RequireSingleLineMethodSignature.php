<?php

declare(strict_types = 1);

namespace Example\Classes;

final class RequireSingleLineMethodSignature
{

    public function foo(int $a, int $b): int {
        return $a + $b;
    }

} 