<?php

declare(strict_types = 1);

namespace Example\TypeHints;

use Exception;
use Throwable;

final class ReferenceThrowableOnly
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