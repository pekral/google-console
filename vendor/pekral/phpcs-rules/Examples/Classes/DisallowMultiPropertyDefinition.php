<?php

declare(strict_types = 1);

namespace Example\Classes;

use DateTime;

final class DisallowMultiPropertyDefinition
{

    private int $id;

    private string $name;

    private bool $active = true;

    private array $data = [];

    private array $appConfig = [
        'retries' => 3,
        'timeout' => 30,
    ];

    private ?string $optionalName = null;

    private string|int $flexibleValue;

    private DateTime $date;

    private static string $singleComplexProp;

    public function __construct(private string $promotedProp)
    {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

}
