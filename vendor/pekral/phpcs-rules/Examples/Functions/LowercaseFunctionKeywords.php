<?php

declare(strict_types = 1);

namespace Example\Functions;

function lowercaseFunction(): void
{
    // function keyword is lowercase
}

final class LowercaseFunctionKeywords
{

    public function call(): void
    {
        lowercaseFunction();
    }

} 