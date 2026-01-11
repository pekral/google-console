<?php

declare(strict_types = 1);

namespace Example\Classes;

use DateTime;

final class DisallowMultiConstantDefinition
{

    public const string CORRECT_SINGLE = 'single';

    public const array SINGLE_ARRAY = [
        'items' => ['one', 'two', 'three'],
        'nested' => [
            'deep' => 'value',
        ],
    ];

    public const string CLASS_REF = DateTime::class;

    public const int EXPRESSION = 2 + 2;

    public final const string FINAL_CONST = 'final';

    #[Attribute]
    public const string ATTRIBUTED = 'with_attribute';

    protected const array COMPLEX_VALUE = [
        'key' => 'value',
        'number' => 123,
    ];

    private const int PRIVATE_CONST = 42;

    public function __construct(public string $promotedProp)
    {
    }

    public function getConstant(): string
    {
        return self::CORRECT_SINGLE;
    }

}

#[Attribute]
final class Attribute
{

    public function __construct(public string $name = '')
    {
    }

}