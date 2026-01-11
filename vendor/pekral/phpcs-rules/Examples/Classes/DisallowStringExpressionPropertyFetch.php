<?php

declare(strict_types = 1);

namespace Example\Classes;

final class DisallowStringExpressionPropertyFetch
{

    private array $data = [
        'bar' => 456,
        'foo' => 123,
    ];

    public function getFoo(): int
    {
        return $this->data['foo'];
    }

} 