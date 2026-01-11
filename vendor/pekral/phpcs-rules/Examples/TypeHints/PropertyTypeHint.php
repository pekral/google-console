<?php

declare(strict_types = 1);

namespace Example\TypeHints;

final class PropertyTypeHint
{

    public function __construct(private int $foo, private string $bar)
    {
    }

} 