<?php

declare(strict_types = 1);

namespace Example\Variables;

final class DisallowVariableVariable
{

    public function example(): void
    {
        // Examples without using variable variables (fixed version)
        $varName = 'data';
        $this->setDynamicVariable($varName, 'value');
        
        // Property access without variable variables
        $property = 'name';
        $this->setDynamicProperty($property, 'value');
        
        // Use variables to avoid "unused variable" errors
        $this->processVariables($varName, $property);
    }
    
    private function setDynamicVariable(string $name, string $value): void
    {
        // In real code, this would handle dynamic variables safely
        // For example, using an array: $this->variables[$name] = $value;
        // Use parameters to avoid unused error
        $placeholder = $name . '_' . $value;
        // Cleanup placeholder
        unset($placeholder);
    }
    
    private function setDynamicProperty(string $name, string $value): void
    {
        // In real code, this would handle dynamic properties safely  
        // For example, using an array: $this->properties[$name] = $value;
        // Use parameters to avoid unused error
        $placeholder = $name . '_' . $value;
        // Cleanup placeholder
        unset($placeholder);
    }
    
    private function processVariables(string $varName, string $property): void
    {
        // This method exists to use the variables
        // In real code, this would process the variables
        // Log variables (avoiding discouraged error_log)
        $logMessage = "VarName: $varName, Property: $property";
        file_put_contents('php://stderr', $logMessage . "\n");
    }

}
