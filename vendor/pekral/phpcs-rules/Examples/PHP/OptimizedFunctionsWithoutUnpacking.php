<?php

declare(strict_types = 1);

namespace Example\PHP;

final class OptimizedFunctionsWithoutUnpacking
{

    public function foo(): int
    {
        return max(1, 2, 3);
    }

} 