<?php

declare(strict_types = 1);

namespace Example\Classes;

trait HelperT
{

    public function help(): string
    {
        return 'help';
    }

}

final class UseTrait
{

    use HelperT;

} 