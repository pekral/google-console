<?php

declare(strict_types = 1);

namespace Example\Operators;

final class RequireCombinedAssignmentOperator
{

    public function foo(): int
    {
        $a = 1;
        $a += 2;

        return $a;
    }

} 