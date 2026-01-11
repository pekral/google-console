<?php

declare(strict_types = 1);

namespace Example\TypeHints;

final class PropertyTypeHintMissingNativeTypeHint
{

    /**
     * @var int
     */
    private $foo;

    public function __construct(int $foo)
    {
        $this->foo = $foo;
    }

    public function getFoo(): int
    {
        return $this->foo;
    }

} 