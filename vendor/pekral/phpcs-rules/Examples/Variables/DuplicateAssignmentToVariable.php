<?php

declare(strict_types = 1);

namespace Example\Variables;

final class DuplicateAssignmentToVariable
{

    public function foo(): int
    {
        $a = 1;
        $b = 2;
        $c = 3;
        
        $result = $a + $b;

        return $result + $c;
    }
    
    public function processData(): void
    {
        $data = ['item1', 'item2'];
        $processedData = [];
        
        foreach ($data as $item) {
            $processedData[] = strtoupper($item);
        }
        
        $this->outputData($processedData);
    }
    
    private function outputData(array $data): void
    {
        foreach ($data as $item) {
            echo $item;
        }
    }

} 