<?php

declare(strict_types = 1);

namespace Example\Functions;

final class FunctionLength
{

    public function shortFunction(): void
    {
        // This function is intentionally short and valid
    }

    public function longFunction(): void
    {
        // This function is intentionally long but still valid
        for ($i = 0; $i < 10; $i += 1) {
            echo $i;
        }
    }

} 