<?php

declare(strict_types = 1);

namespace Example\TypeHints;

use stdClass;

final class LongTypeHints
{

    public function foo(): void
    {
        $this->processData('test');
        $this->handleArray([1, 2, 3]);
        $this->processObject(new stdClass());
    }
    
    private function processData(string $data): void
    {
        echo "Processing: $data";
    }
    
    private function handleArray(array $data): void
    {
        foreach ($data as $item) {
            echo $item;
        }
    }
    
    private function processObject(object $obj): void
    {
        echo "Object: " . $obj::class;
    }

} 