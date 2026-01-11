<?php

declare(strict_types = 1);

namespace Example\TypeHints;

final class DisallowArrayTypeHintSyntax
{

    public function foo(): void
    {
        $this->processData(['item1', 'item2']);
        $this->processAssocArray(['key' => 'value']);
        $this->processNestedArray([['nested' => 'data']]);
    }
    
    private function processData(array $data): void
    {
        foreach ($data as $item) {
            echo $item;
        }
    }
    
    private function processAssocArray(array $data): void
    {
        foreach ($data as $key => $value) {
            echo "$key: $value";
        }
    }
    
    private function processNestedArray(array $data): void
    {
        foreach ($data as $nested) {
            foreach ($nested as $key => $value) {
                echo "$key: $value";
            }
        }
    }

} 