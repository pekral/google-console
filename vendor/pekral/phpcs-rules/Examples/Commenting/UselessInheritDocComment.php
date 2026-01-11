<?php

declare(strict_types = 1);

namespace Example\Commenting;

interface Base
{

    public function foo(): void;

}

final class UselessInheritDocComment implements Base
{

    public function foo(): void
    {
        // intentionally left blank
    }

} 