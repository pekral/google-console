<?php

declare(strict_types = 1);

namespace Example\TypeHints;

final class UnionTypeHintFormat
{

    public function processData(string|int $data): string|int
    {
        if (is_string($data)) {
            return strtoupper($data);
        }
        
        return $data * 2;
    }

    public function processNullableData(?string $data): ?string
    {
        if ($data === null) {
            return null;
        }
        
        return strtoupper($data);
    }

    public function processDataWithNullLast(null|string|int $data): null|string|int
    {
        if ($data === null) {
            return null;
        }
        
        if (is_string($data)) {
            return strtoupper($data);
        }
        
        return $data * 2;
    }

    public function processComplexData(string|int|float $value, null|array|object $config): null|string|int|float|array|object
    {
        if ($config === null) {
            return $value;
        }
        
        if (is_array($config)) {
            return $config;
        }
        
        return $value;
    }

}

final class DataProcessor
{

    private string|int $value1;
    private ?string $name1;
    private null|array|object $config1;

    public function __construct(private string|int $value, private ?string $name = null, private null|array|object $config = null)
    {
    }

}

final class ArrowFunctionExample
{

    public function processWithArrowFunction(): void
    {
        $processor = static fn (string|int $data): string|int => is_string($data) ? strtoupper($data) : $data * 2;

        $result1 = $processor('hello');
        $result2 = $processor(42);
        
        echo $result1 . $result2;
    }

}
