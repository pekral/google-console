<?php

declare(strict_types = 1);

namespace Example\Classes;

final class PropertyDeclaration
{

    public function __construct(private int $foo, private string $bar)
    {
    }

    public function getFoo(): int
    {
        return $this->foo;
    }

    public function getBar(): string
    {
        return $this->bar;
    }

} 