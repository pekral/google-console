<?php

declare(strict_types = 1);

namespace Example\Exceptions;

use Exception;
use Throwable;

final class RequireNonCapturingCatch
{

    public function foo(): void
    {
        try {
            throw new Exception('error');
        } catch (Throwable) {
            echo 'error';
        }
    }

} 