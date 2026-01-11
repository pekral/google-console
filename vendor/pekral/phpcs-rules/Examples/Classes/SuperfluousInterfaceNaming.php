<?php

declare(strict_types = 1);

namespace Example\Classes;

interface Base
{

    public function foo(): void;

}

final class Concrete implements Base
{

    public function foo(): void
    {
        // intentionally left blank
    }

} 