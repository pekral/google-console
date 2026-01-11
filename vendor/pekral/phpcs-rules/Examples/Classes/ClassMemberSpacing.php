<?php

declare(strict_types = 1);

namespace Example\Classes;

final class ClassMemberSpacing
{

    public function __construct(private int $foo)
    {
    }

    public function getFoo(): int
    {
        return $this->foo;
    }

} 