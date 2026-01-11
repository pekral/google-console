<?php

declare(strict_types = 1);

namespace Example\Miscellaneous;

final class DiscouragedFunctions
{

    public function foo(): void
    {
        // No discouraged functions like var_dump, die, etc.
        echo 'ok';
    }

} 