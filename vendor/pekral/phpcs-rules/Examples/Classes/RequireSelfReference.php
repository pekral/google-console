<?php

declare(strict_types = 1);

namespace Example\Classes;

final class RequireSelfReference
{

    public const int FOO = 42;

    public function getFoo(): int
    {
        return self::FOO;
    }

} 