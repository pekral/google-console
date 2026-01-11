<?php

declare(strict_types = 1);

namespace Example\Namespaces;

// Valid: No group use, only individual use statements
use DateTime;
use Exception;

final class GroupUseExample
{

    public function foo(): string
    {
        $dt = new DateTime();
        $ex = new Exception();

        return $dt->format('c') . $ex->getMessage();
    }

} 