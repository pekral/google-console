<?php

declare(strict_types = 1);

namespace Example\PHP;

/**
 * Examples demonstrating the DisallowReference rule.
 * 
 * This rule disallows usage of references in PHP code and reports errors for:
 * - Passing by reference (e.g., function foo(&$param))
 * - Returning references (e.g., function &foo())
 * - Inheriting variables by reference in closures (e.g., use (&$var))
 * - Assigning by reference (e.g., $ref = &$var)
 */
final class DisallowReference
{

    /**
     * Demonstrates disallowed returning references.
     */
    public function disallowedReturningReference(): int
    {
        return 42;
    }

}
