<?php

declare(strict_types = 1);

namespace Pekral\GoogleConsole\Helper;

use Stringable;

final class TypeHelper
{

    public static function toFloat(mixed $value): float
    {
        return is_numeric($value) ? (float) $value : 0.0;
    }

    /**
     * @return array<string>
     */
    public static function toStringArray(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return array_map(
            static fn (mixed $item): string => is_scalar($item) || $item instanceof Stringable ? (string) $item : '',
            array_values($value),
        );
    }

}
