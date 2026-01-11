<?php

declare(strict_types = 1);

namespace Example\TypeHints;

use stdClass;

final class DisallowVariableVariable
{

    public function foo(): int
    {
        $this->processData('test');
        $this->handleArray(['key1' => 'value1', 'key2' => 'value2']);
        $this->processObject(new stdClass());
        
        return 1;
    }
    
    private function processData(string $data): void
    {
        echo "Processing: $data";
    }
    
    private function handleArray(array $data): void
    {
        foreach ($data as $key => $value) {
            echo "$key: $value";
        }
    }
    
    private function processObject(object $obj): void
    {
        echo "Object: " . $obj::class;
    }

} 