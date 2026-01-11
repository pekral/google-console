<?php

declare(strict_types = 1);

namespace Example\Classes;

final class RequireMultiLineMethodSignature
{

    public function foo(int $a, int $b, int $c, int $d, int $e, int $f, int $g, int $h, int $i, int $j): int {
        return $a + $b + $c + $d + $e + $f + $g + $h + $i + $j;
    }

} 