<?php

declare(strict_types = 1);

namespace Example\ControlStructures;

final class UselessIfConditionWithReturn
{

    public function example(int $value): string
    {
        if ($value > 0) {
            return 'positive';
        }
        
        return 'negative';
    }

}
