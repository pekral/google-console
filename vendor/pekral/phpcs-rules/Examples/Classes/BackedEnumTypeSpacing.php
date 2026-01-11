<?php

declare(strict_types = 1);

namespace Example\Classes;

enum Status: string
{

    case Active = 'active';
    case Inactive = 'inactive';

}

final class BackedEnumTypeSpacing
{

    public function getStatus(): Status
    {
        return Status::Active;
    }

} 