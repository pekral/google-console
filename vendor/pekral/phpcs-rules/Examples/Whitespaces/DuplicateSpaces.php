<?php

declare(strict_types = 1);

namespace Example\Whitespaces;

final class DuplicateSpaces
{

    public function example(): void
    {
        $this->processData();
        $this->formatOutput();
        $this->validateInput();
    }
    
    private function processData(): void
    {
        $data = ['item1', 'item2'];
        
        foreach ($data as $item) {
            echo $item;
        }
    }
    
    private function formatOutput(): void
    {
        $message = 'Hello World';
        $formatted = strtoupper($message);
        
        echo $formatted;
    }
    
    private function validateInput(): void
    {
        $input = 'test';
        
        if (strlen($input) > 0) {
            echo 'Valid input';
        }
    }

}
