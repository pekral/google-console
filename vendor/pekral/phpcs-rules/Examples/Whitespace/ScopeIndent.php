<?php

declare(strict_types = 1);

namespace Example\Whitespace;

final class ScopeIndent
{

    public function example(): void
    {
        $data = ['item1', 'item2', 'item3'];
        
        foreach ($data as $item) {
            if (strlen($item) > 4) {
                $processed = strtoupper($item);
                echo $processed;
            } else {
                echo $item;
            }
        }
        
        $this->processData($data);
    }
    
    private function processData(array $data): void
    {
        $result = [];
        
        for ($i = 0; $i < count($data); $i += 1) {
            if ($i % 2 === 0) {
                $result[] = $data[$i];
            }
        }
        
        foreach ($result as $item) {
            echo $item;
        }
    }
    
    private function nestedConditions(): void
    {
        $value = 42;
        
        if ($value <= 0) {
            echo 'Number 0 or negative';

            return;
        }
        
        if ($value >= 100) {
            echo 'Number 100 or greater';

            return;
        }
        
        if ($value % 2 === 0) {
            echo 'Even number between 0 and 100';
        } else {
            echo 'Odd number between 0 and 100';
        }
    }

}
