<?php

declare(strict_types = 1);

namespace Example\ControlStructures;

final class JumpStatementsSpacing
{

    public function foo(int $a): void
    {
        if ($a < 0) {
            return;
        }

        echo $a;
    }

} 