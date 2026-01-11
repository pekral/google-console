<?php

declare(strict_types = 1);

namespace Example\Whitespace;

final class ConcatenationSpacing
{

    public function foo(string $a, string $b): string
    {
        // Correct spacing around concatenation operator
        return $a . ' ' . $b;
    }

} 