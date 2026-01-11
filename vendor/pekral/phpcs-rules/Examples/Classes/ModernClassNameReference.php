<?php

declare(strict_types = 1);

namespace Example\Classes;

final class ModernClassNameReference
{

    public function foo(): string
    {
        return self::class;
    }

} 