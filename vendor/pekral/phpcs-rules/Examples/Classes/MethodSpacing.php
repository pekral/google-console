<?php

declare(strict_types = 1);

namespace Example\Classes;

final class MethodSpacing
{

    public function foo(): void {
        // Next time
    }

    public function bar(): void
    {
        // intentionally left blank
    }

    public function baz(): void
    {
        // intentionally left blank
    }

} 