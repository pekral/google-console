<?php

declare(strict_types = 1);

namespace Example\TypeHints;

final class StaticClosure
{

    public function foo(): void
    {
        $fn = static function (): void {
            echo 'bar';
        };
        $fn();
    }

} 