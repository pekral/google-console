<?php

declare(strict_types = 1);

namespace Example\Functions;

/**
 * Examples demonstrating the DisallowTrailingCommaInDeclaration rule.
 * 
 * This rule disallows trailing commas after the last parameter in function declarations.
 * It applies to functions, methods, closures, and arrow functions.
 */
final class DisallowTrailingCommaInDeclaration
{

    /**
     * Demonstrates disallowed trailing comma in function declaration.
     */
    public function functionWithTrailingComma(string $param1, int $param2, array $param3,): void {
        // Use parameters to avoid unused parameter errors
        $result = $param1 . $param2 . implode(',', $param3);
        echo $result;
    }

    /**
     * Demonstrates disallowed trailing comma in closure declaration.
     */
    public function closureWithTrailingComma(): void
    {
        $closure = static fn (
            string $param1,
            int $param2,
            array $param3,
        ) => $param1 . $param2 . implode(',', $param3);
        
        echo $closure('test', 42, ['a', 'b', 'c']);
    }

    /**
     * Demonstrates disallowed trailing comma in arrow function declaration.
     */
    public function arrowFunctionWithTrailingComma(): void
    {
        $arrowFunction = static fn (
            string $param1,
            int $param2,
            array $param3,
        ) => $param1 . $param2 . implode(',', $param3);
        
        echo $arrowFunction('test', 42, ['x', 'y', 'z']);
    }

}
