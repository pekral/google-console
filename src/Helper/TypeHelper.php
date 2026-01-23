<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\Helper;

final class TypeHelper
{

    public static function toFloat(mixed $value): float
    {
        return is_numeric($value) ? (float) $value : 0.0;
    }

}
