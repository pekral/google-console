<?php

declare(strict_types = 1);

namespace Example\Functions;

use stdClass;

/**
 * Examples demonstrating the DisallowTrailingCommaInClosureUse rule.
 *
 * This rule disallows trailing commas after the last inherited variable in "use" of closure declaration.
 * It applies only to closure functions with use clauses.
 */
final class DisallowTrailingCommaInClosureUse
{

    /**
     * Demonstrates disallowed trailing comma in closure use clause.
     */
    public function closureWithTrailingCommaInUse(): void
    {
        $value = 42;
        $array = [1, 2, 3];
        $object = new stdClass();
        
        $closure = static fn () => $value . implode(',', $array) . $object::class;
        
        echo $closure();
    }

    /**
     * Demonstrates disallowed trailing comma in multi-line closure use clause.
     */
    public function multiLineClosureWithTrailingCommaInUse(): void
    {
        $name = 'John';
        $age = 25;
        $city = 'Prague';
        $country = 'Czech Republic';
        
        $closure = static fn () => "Name: $name, Age: $age, City: $city, Country: $country";
        
        echo $closure();
    }

    /**
     * Demonstrates disallowed trailing comma in static closure use clause.
     */
    public function staticClosureWithTrailingCommaInUse(): void
    {
        $config = ['debug' => true, 'log' => false];
        $env = 'production';
        
        $staticClosure = static fn () => $config['debug'] ? "Debug mode in $env" : "Production mode";
        
        echo $staticClosure();
    }

}
