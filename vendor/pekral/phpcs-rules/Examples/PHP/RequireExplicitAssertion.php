<?php

declare(strict_types = 1);

namespace Example\PHP;

use DateTime;
use stdClass;

use function assert;
use function is_float;
use function is_int;
use function is_string;

final class RequireExplicitAssertion
{

    public function variableWithVarAnnotation(): void
    {
        $name = 'John';
        assert(is_string($name));
        $age = 25;
        assert(is_int($age));
        $date = new DateTime();
        assert($date instanceof DateTime);
    }

    public function foreachWithVarAnnotation(): void
    {
        $users = ['John', 'Jane', 'Bob'];

        foreach ($users as $user) {
            assert(is_string($user));
            echo $user;
        }
        
        $objects = [new stdClass(), new DateTime()];

        foreach ($objects as $object) {
            assert($object instanceof stdClass || $object instanceof DateTime);
            echo $object::class;
        }
    }

    public function listWithVarAnnotation(): void
    {
        $data = ['John', 25];
        [$name, $age] = $data;
        assert(is_string($name));
        assert(is_int($age));
    }

    public function complexTypeAnnotations(): void
    {
        $nullableString = 'hello';
        assert(is_string($nullableString) || $nullableString === null);
        $numericValue = 42;
        assert(is_int($numericValue) || is_float($numericValue));
        $intersectionType = new class extends stdClass {

        };
        assert($intersectionType instanceof stdClass && $intersectionType instanceof DateTime);
    }

    public function advancedTypeAnnotations(): void
    {
        $nonEmptyString = 'hello';
        echo $nonEmptyString;
        
        $callableString = 'strlen';
        echo $callableString;
        
        $numericString = '123';
        echo $numericString;
    }

    public function integerRangeAnnotations(): void
    {
        $positiveInt = 5;
        echo $positiveInt;
        
        $negativeInt = -5;
        echo $negativeInt;
        
        $rangeInt = 50;
        echo $rangeInt;
    }

    public function correctExplicitAssertions(): void
    {
        $name = 'John';
        $age = 25;
        $date = new DateTime();
        
        assert(is_string($name));
        assert(is_int($age));
        assert($date instanceof DateTime);
        
        $nullableString = 'hello';
        assert(is_string($nullableString) || $nullableString === null);
        
        $numericValue = 42;
        assert(is_int($numericValue) || is_float($numericValue));
    }

    public function ignoredCases(): void
    {
        $simpleVariable = 'value';
        echo $simpleVariable;
        $unnamedVar = 'value';
        assert(is_string($unnamedVar));
        echo $unnamedVar;
        
        $mixedVar = 'value';
        echo $mixedVar;
        
        $voidVar = null;
        echo $voidVar;
    }

    public function otherAnnotations(): void
    {
        echo 'Method with other annotations';
    }
    
    public function methodWithOtherAnnotations(string $param): void
    {
        echo $param;
    }

}
