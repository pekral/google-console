<?php

declare(strict_types = 1);

namespace Example\Classes;

trait Logger
{

    public function log(): void
    {
        // log message
    }

}

final class TraitUseDeclaration
{

    use Logger;

    public function foo(): void
    {
        $this->log();
    }

} 