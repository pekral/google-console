<?php

declare(strict_types = 1);

namespace Example\PHP;

final class DisallowDirectMagicInvokeCall
{

    public function foo(): void
    {
        // Does not use __invoke
        echo 'ok';
    }

} 