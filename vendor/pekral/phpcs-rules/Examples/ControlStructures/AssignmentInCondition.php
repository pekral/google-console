<?php

declare(strict_types = 1);

namespace Example\ControlStructures;

final class AssignmentInCondition
{

    public function example(int $value): string
    {
        if ($value > 0) {
            return 'positive';
        }
        
        return 'negative';
    }

}
