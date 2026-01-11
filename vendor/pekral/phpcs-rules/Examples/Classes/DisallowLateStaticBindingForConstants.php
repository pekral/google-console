<?php

declare(strict_types = 1);

namespace Example\Classes;

final class DisallowLateStaticBindingForConstants
{

    public const int FOO = 1;

    public function getFoo(): int
    {
        return self::FOO;
    }

} 