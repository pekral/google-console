<?php

declare(strict_types = 1);

namespace Example\TypeHints;

final class DisallowEmptyFunction
{

    public function foo(): void
    {
        echo 'bar';
    }

} 