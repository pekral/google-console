<?php

declare(strict_types = 1);

namespace Example\ControlStructures;

use stdClass;

final class NewWithParentheses
{

    public function foo(): void
    {
        new stdClass();
    }

} 