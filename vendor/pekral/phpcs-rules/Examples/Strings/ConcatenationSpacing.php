<?php

declare(strict_types = 1);

namespace Example\Strings;

final class ConcatenationSpacing
{

    public function example(): string
    {
        $firstName = 'John';
        $lastName = 'Doe';
        
        $fullName = $firstName . ' ' . $lastName;
        
        $greeting = 'Hello, ' . $fullName . '!';
        
        $message = 'Welcome to ' . $this->getLocation() . ' on ' . date('Y-m-d');
        
        return $greeting . ' ' . $message;
    }
    
    private function getLocation(): string
    {
        return 'PHP World';
    }

}
