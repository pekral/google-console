<?php

declare(strict_types = 1);

namespace Example\TypeHints;

final class UnusedInheritedVariablePassedToClosure
{

    public function foo(): void
    {
        $a = 1;
        $fn = static function () use ($a): void {
            echo $a;
        };
        $fn();
    }

} 