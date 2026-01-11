<?php

declare(strict_types = 1);

namespace Example\Namespaces;

use DateTime;

// No group use here, only individual use statements
final class DisallowGroupUse
{

    public function foo(): string
    {
        $dt = new DateTime();

        return $dt->format('c');
    }

} 