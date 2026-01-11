<?php

declare(strict_types = 1);

namespace Example\Variables;

final class UselessVariable
{

    public function foo(): int
    {
        return $this->calculateValue(5, 3);
    }
    
    public function processData(): void
    {
        $this->outputResult($this->getData());
    }
    
    public function validateInput(string $input): bool
    {
        return strlen($input) > 0;
    }
    
    private function calculateValue(int $a, int $b): int
    {
        return $a + $b;
    }
    
    private function getData(): array
    {
        return ['item1', 'item2'];
    }
    
    private function outputResult(array $data): void
    {
        foreach ($data as $item) {
            echo $item;
        }
    }

} 