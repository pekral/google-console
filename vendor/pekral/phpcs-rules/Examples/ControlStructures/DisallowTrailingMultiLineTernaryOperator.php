<?php

declare(strict_types = 1);

namespace Example\ControlStructures;

final class DisallowTrailingMultiLineTernaryOperator
{

    public function example(int $value): string
    {
        if ($value > 0) {
            return 'positive';
        }
        
        return 'negative';
    }

}
