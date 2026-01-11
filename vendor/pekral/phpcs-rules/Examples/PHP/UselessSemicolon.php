<?php

declare(strict_types = 1);

namespace Example\PHP;

final class UselessSemicolon
{

    public function foo(): void
    {
        $value = 'ok';
        echo $value;
        
        $array = [1, 2, 3];

        foreach ($array as $item) {
            echo $item;
        }
        
        $result = $this->processData('test');
        echo $result;
    }
    
    private function processData(string $data): string
    {
        return strtoupper($data);
    }

} 