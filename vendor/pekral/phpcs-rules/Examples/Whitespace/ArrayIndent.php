<?php

declare(strict_types = 1);

namespace Example\Whitespace;

final class ArrayIndent
{

    public function foo(): array
    {
        return [
            'a' => 1,
            'b' => 2,
            'c' => 3,
        ];
    }

} 