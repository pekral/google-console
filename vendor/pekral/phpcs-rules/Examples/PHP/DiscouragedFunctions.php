<?php

declare(strict_types = 1);

namespace Example\PHP;

final class DiscouragedFunctions
{

    public function example(): void
    {
        $value = 'test';
        
        // Use proper alternatives instead of discouraged functions
        if ($value !== '') {
            echo $value;
        }
        
        $array = [1, 2, 3];

        if (count($array) > 0) {
            echo 'Array has elements';
        }
        
        $result = $this->processData($value);
        echo $result;
    }
    
    private function processData(string $data): string
    {
        return strtoupper($data);
    }

}
