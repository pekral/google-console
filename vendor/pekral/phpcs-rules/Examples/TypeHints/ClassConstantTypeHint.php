<?php

declare(strict_types = 1);

namespace Example\TypeHints;

final class ClassConstantTypeHint
{

    public const int FOO = 1;
    public const string BAR = 'bar';

    public function getFoo(): int
    {
        return self::FOO;
    }

    public function getBar(): string
    {
        return self::BAR;
    }

} 