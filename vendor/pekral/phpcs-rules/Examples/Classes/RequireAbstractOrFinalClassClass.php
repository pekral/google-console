<?php

declare(strict_types = 1);

namespace Example\Classes;

// Valid: class is final
final class RequireAbstractOrFinalClass
{

    public function foo(): void
    {
        echo 'This class is final.';
    }

}

// Valid: class is abstract
abstract class RequireAbstractOrFinalAbstractClass
{

    abstract public function bar(): void;

} 