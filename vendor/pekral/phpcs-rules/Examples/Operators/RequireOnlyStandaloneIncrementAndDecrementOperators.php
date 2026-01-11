<?php

declare(strict_types = 1);

namespace Example\Operators;

final class RequireOnlyStandaloneIncrementAndDecrementOperators
{

    public function foo(): int
    {
        $a = 1;
        $b = 5;
        $c = 10;
        $d = 20;

        // Correct usage - standalone instructions
        $a++;
        ++$b;
        $c--;
        --$d;

        // Correct usage in for loop
        for ($i = 0; $i < 5; $i++) {
            $a += $i;
        }

        // Correct usage in while loop
        while ($b > 0) {
            $b--;
        }

        return $a + $b + $c + $d;
    }

}
