<?php

declare(strict_types = 1);

namespace Example\Whitespace;

final class DuplicateSpaces
{

    public function foo(): int
    {
        // No duplicate spaces here
        $a = 1;
        $b = 2;

        return $a + $b;
    }

} 