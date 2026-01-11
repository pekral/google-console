<?php

declare(strict_types = 1);

namespace Example\Classes;

trait LoggerSpacing
{

    public function log(): void
    {
        // log message
    }

}

final class TraitUseSpacing
{

    use LoggerSpacing;

    public function foo(): void
    {
        $this->log('foo');
    }

} 