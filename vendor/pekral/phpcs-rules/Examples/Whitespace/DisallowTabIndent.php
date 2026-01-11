<?php

declare(strict_types = 1);

namespace Example\Whitespace;

final class DisallowTabIndent
{

    public function example(): void
    {
        $this->processData();
        $this->handleArray();
        $this->formatOutput();
    }
    
    private function processData(): void
    {
        $data = ['item1', 'item2'];
        
        foreach ($data as $item) {
            echo $item;
        }
    }
    
    private function handleArray(): void
    {
        $array = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];
        
        foreach ($array as $key => $value) {
            echo "$key: $value";
        }
    }
    
    private function formatOutput(): void
    {
        $message = 'Hello World';
        
        if (strlen($message) > 0) {
            echo $message;
        }
    }

}
