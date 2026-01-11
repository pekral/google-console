<?php

declare(strict_types = 1);

namespace Example\TypeHints;

final class NullableTypeForNullDefaultValue
{

    public function example(string $param): int
    {
        return $this->processData($param, null);
    }
    
    public function withNullableParam(?string $param = null): string
    {
        return $param ?? 'default';
    }
    
    public function withNullableReturn(?string $data): ?string
    {
        if ($data === null) {
            return null;
        }
        
        return strtoupper($data);
    }
    
    private function processData(string $data, ?string $optional = null): int
    {
        $result = strlen($data);
        
        if ($optional !== null) {
            $result += strlen($optional);
        }
        
        return $result;
    }

}
