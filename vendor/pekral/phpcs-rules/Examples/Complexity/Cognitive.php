<?php

declare(strict_types = 1);

namespace Example\Complexity;

final class Cognitive
{

    public function simpleMethod(array $data): array
    {
        $result = [];

        foreach ($data as $item) {
            if ($item['status'] === 'active') {
                $result[] = $item;
            }
        }

        return $result;
    }

    public function mediumComplexityMethod(array $data, bool $flag): array
    {
        $result = [];

        foreach ($data as $item) {
            if ($item['status'] === 'active' && ($flag ? $item['priority'] > 5 : $item['priority'] <= 5)) {
                $result[] = $item;
            }
        }

        return $result;
    }

}
