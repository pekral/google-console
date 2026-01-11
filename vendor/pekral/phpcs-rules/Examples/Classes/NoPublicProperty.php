<?php

declare(strict_types = 1);

namespace Example\Classes;

final class NoPublicProperty
{

    private int $foo = 1;

    public function getFoo(): int {
        return $this->foo;
    }

} 