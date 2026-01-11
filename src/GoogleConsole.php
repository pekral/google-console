<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole;

final class GoogleConsole
{

    public function greet(string $name): string
    {
        return sprintf('Hello, %s!', $name);
    }

}
