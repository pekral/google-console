<?php

declare(strict_types = 1);

namespace Example\Classes;

final class RequireConstructorPropertyPromotion
{

    public const string VERSION = '1.0';

    public function __construct(private int $id)
    {
    }

    public function getId(): int
    {
        return $this->id;
    }

}
