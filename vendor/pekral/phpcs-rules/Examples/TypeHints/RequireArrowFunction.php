<?php

declare(strict_types = 1);

namespace Example\TypeHints;

final class RequireArrowFunction
{

    public function foo(): int
    {
        $fn = static fn (int $a): int => $a + 1;

        return $fn(1);
    }

} 