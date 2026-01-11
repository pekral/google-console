<?php

declare(strict_types = 1);

namespace Example\Arrays;

final class DisallowImplicitArrayCreation
{

    public function getArray(): array
    {
        return [1, 2, 3];
    }

} 