<?php

declare(strict_types = 1);

namespace Example\ControlStructures;

final class DisallowEmpty
{

    public function foo(string $a): bool
    {
        return $a !== '';
    }

} 