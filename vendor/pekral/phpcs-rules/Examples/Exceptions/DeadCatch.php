<?php

declare(strict_types = 1);

namespace Example\Exceptions;

use Exception;
use Throwable;

final class DeadCatch
{

    public function foo(): void
    {
        try {
            throw new Exception('error');
        } catch (Throwable $e) {
            echo $e->getMessage();
        }
    }

} 