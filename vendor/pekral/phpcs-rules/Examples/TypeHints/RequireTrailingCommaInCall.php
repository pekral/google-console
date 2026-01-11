<?php

declare(strict_types = 1);

namespace Example\TypeHints;

final class RequireTrailingCommaInCall
{

    public function foo(int $a, int $b): int
    {
        return $a + $b;
    }

    public function bar(): int
    {
        return $this->foo(1, 2);
    }

} 