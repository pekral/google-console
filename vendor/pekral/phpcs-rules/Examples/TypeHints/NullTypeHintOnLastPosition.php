<?php

declare(strict_types = 1);

namespace Example\TypeHints;

final class NullTypeHintOnLastPosition
{

    public function foo(): void
    {
        $this->processData('test');
        $this->handleNullableString('hello');
        $this->processNullableArray([1, 2, 3]);
    }
    
    public function withNullableParam(?string $param = null): string
    {
        return $param ?? 'default';
    }
    
    public function withNullableReturn(?string $data): ?string
    {
        if ($data === null) {
            return null;
        }
        
        return strtoupper($data);
    }
    
    private function processData(string $data): void
    {
        echo "Processing: $data";
    }
    
    private function handleNullableString(?string $text): void
    {
        if ($text !== null) {
            echo "Text: $text";
        }
    }
    
    private function processNullableArray(?array $data): void
    {
        if ($data !== null) {
            foreach ($data as $item) {
                echo $item;
            }
        }
    }

} 