<?php

declare(strict_types = 1);

namespace Example\TypeHints;

final class MissingNativeTypeHint
{

    public function example(string $param): int
    {
        return strlen($param);
    }

}
