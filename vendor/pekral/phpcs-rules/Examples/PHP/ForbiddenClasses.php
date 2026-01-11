<?php

declare(strict_types = 1);

namespace Example\PHP;

use DateTimeImmutable;
use stdClass;

final class ForbiddenClasses
{

    public function foo(): void
    {
        // Use proper alternatives instead of forbidden classes
        $date = new DateTimeImmutable();
        echo $date->format('Y-m-d');
        
        $object = new stdClass();
        $object->property = 'value';
        echo $object->property;
        
        $result = $this->processData('test');
        echo $result;
    }
    
    private function processData(string $data): string
    {
        return strtoupper($data);
    }

} 