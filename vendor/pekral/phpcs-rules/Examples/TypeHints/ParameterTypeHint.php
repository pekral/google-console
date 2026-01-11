<?php

declare(strict_types = 1);

namespace Example\TypeHints;

final class ParameterTypeHint
{

    public function foo(int $a, string $b): void
    {
        // use the parameters
        echo $a;
        echo $b;
    }

} 