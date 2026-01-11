<?php

declare(strict_types = 1);

namespace Example\Arrays;

final class DisallowPartiallyKeyed
{

    public function getArray(): array
    {
        return [
            'a' => 1,
            'b' => 2,
        ];
    }

} 