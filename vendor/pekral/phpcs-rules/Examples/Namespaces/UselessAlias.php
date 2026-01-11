<?php

declare(strict_types = 1);

namespace Example\Namespaces;

use DateTime;

final class UselessAlias
{

    public function foo(DateTime $dt): void
    {
        echo $dt->format('c');
    }

} 