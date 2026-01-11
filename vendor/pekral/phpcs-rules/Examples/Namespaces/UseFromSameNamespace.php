<?php

declare(strict_types = 1);

namespace Example\Namespaces;

final class Helper {

}

final class UseFromSameNamespace
{

    public function foo(Helper $h): void
    {
        // use the parameter
        echo $h::class;
    }

} 