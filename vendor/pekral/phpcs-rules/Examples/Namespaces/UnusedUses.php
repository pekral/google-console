<?php

declare(strict_types = 1);

namespace Example\Namespaces;

use DateTime;

final class UnusedUses
{

    public function foo(DateTime $dt): void
    {
        // use the parameter
        echo $dt->format('c');
    }

} 