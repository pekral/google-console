<?php

declare(strict_types = 1);

namespace Example\Arrays;

final class ArrayAccess
{

    public function getFirst(array $arr): int
    {
        return $arr[0];
    }

} 