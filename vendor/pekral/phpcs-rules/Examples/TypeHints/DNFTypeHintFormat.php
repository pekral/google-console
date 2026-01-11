<?php

declare(strict_types = 1);

namespace Example\TypeHints;

final class DNFTypeHintFormat
{

    /**
     * @param array<int|string> $values
     */
    public function foo(array $values): int|string
    {
        return $values[0];
    }

} 