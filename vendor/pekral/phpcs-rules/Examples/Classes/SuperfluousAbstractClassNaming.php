<?php

declare(strict_types = 1);

namespace Example\Classes;

abstract class Base
{

    abstract public function foo(): void;

}

final class Concrete extends Base
{

    public function foo(): void
    {
        // intentionally left blank
    }

} 