<?php

declare(strict_types = 1);

namespace Example\PHP;

final class RequireNowdoc
{

    public function foo(): string
    {
        return <<<'NOW'
Toto je nowdoc
NOW;
    }

} 