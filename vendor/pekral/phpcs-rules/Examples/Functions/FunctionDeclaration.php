<?php

declare(strict_types = 1);

namespace Example\Functions;

function foo(): void
{
    // valid function declaration
}

final class FunctionDeclarationExample
{

    public function bar(): void
    {
        foo();
    }

} 