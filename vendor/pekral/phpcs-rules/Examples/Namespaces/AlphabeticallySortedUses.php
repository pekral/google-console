<?php

declare(strict_types = 1);

namespace Example\Namespaces;

use DateTime;
use Throwable;

final class AlphabeticallySortedUses
{

    public function foo(DateTime $dt, Throwable $t): void
    {
        // use all parameters
        echo $dt->format('c');
        echo $t->getMessage();
    }

} 