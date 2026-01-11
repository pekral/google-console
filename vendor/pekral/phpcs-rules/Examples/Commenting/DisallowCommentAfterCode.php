<?php

declare(strict_types = 1);

namespace Example\Commenting;

final class DisallowCommentAfterCode
{

    public function foo(): void
    {
        // This is a valid comment
        echo 'bar';
    }

} 