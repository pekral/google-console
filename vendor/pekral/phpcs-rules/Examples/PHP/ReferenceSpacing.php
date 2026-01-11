<?php

declare(strict_types = 1);

namespace Example\PHP;

final class ReferenceSpacing
{

    public function example(): void
    {
        $value = 42;
        $array = [1, 2, 3];
        
        // Examples without references (following DisallowReference rule)
        $result = $this->processValue($value);
        echo $result;
        
        // Process array without references
        $processedArray = $this->processArray($array);
        echo implode(', ', $processedArray);
    }
    
    private function processValue(int $data): int
    {
        return $data * 2;
    }
    
    private function processArray(array $data): array
    {
        $result = [];

        foreach ($data as $item) {
            $result[] = $item * 2;
        }
        
        return $result;
    }

}
