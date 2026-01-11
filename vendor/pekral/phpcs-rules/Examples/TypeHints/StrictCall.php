<?php

declare(strict_types = 1);

namespace Example\TypeHints;

use function strlen;

final class StrictCall
{

    public function foo(): void
    {
        strlen('abc');
    }

} 