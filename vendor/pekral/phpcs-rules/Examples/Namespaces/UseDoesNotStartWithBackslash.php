<?php

declare(strict_types = 1);

namespace Example\Namespaces;

// Use statements do not start with backslash
use DateTime;
use Exception;

final class UseDoesNotStartWithBackslash
{

    public function foo(): string
    {
        $dt = new DateTime();
        $ex = new Exception();

        return $dt->format('c') . $ex->getMessage();
    }

} 