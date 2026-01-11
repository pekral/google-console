<?php

declare(strict_types = 1);

namespace Example\Miscellaneous;

final class Cognitive
{

    public function simpleMethod(array $items): array
    {
        $processed = [];

        foreach ($items as $item) {
            if ($item['active']) {
                $processed[] = $item;
            }
        }

        return $processed;
    }

    public function mediumComplexityMethod(array $items, bool $flag): array
    {
        $processed = [];

        foreach ($items as $item) {
            if ($item['active'] && ($flag ? $item['verified'] : true)) {
                $processed[] = $item;
            }
        }

        return $processed;
    }

} 