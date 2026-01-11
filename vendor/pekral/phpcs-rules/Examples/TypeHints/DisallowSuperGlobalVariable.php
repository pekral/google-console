<?php

declare(strict_types = 1);

namespace Example\TypeHints;

final class DisallowSuperGlobalVariable
{

    public function foo(): int
    {
        $this->processData('test data');
        $this->handleRequest(['param' => 'value']);
        $this->processSession(['user_id' => 123]);
        
        return 1;
    }
    
    private function processData(string $data): void
    {
        echo "Processing: $data";
    }
    
    private function handleRequest(array $params): void
    {
        foreach ($params as $key => $value) {
            echo "$key: $value";
        }
    }
    
    private function processSession(array $sessionData): void
    {
        foreach ($sessionData as $key => $value) {
            echo "Session $key: $value";
        }
    }

} 