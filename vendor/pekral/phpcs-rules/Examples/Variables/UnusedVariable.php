<?php

declare(strict_types = 1);

namespace Example\Variables;

final class UnusedVariable
{

    public function foo(): int
    {
        $a = 1;
        echo $a;

        return $a;
    }

} 