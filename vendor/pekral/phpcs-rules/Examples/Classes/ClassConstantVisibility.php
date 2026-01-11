<?php

declare(strict_types = 1);

namespace Example\Classes;

final class ClassConstantVisibility
{

    public const int FOO = 1;
    protected const string BAR = 'bar';
    private const float BAZ = 3.14;

    public function getFoo(): int
    {
        return self::FOO;
    }

    protected function getBar(): string
    {
        return self::BAR;
    }

    private function getBaz(): float
    {
        return self::BAZ;
    }

} 