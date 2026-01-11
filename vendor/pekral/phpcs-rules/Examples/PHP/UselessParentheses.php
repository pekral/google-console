<?php

declare(strict_types = 1);

namespace Example\PHP;

use DateTime;
use Generator;
use stdClass;

final class UselessParentheses
{

    public function complexTernaryCondition(): string
    {
        $a = 1;
        $b = 2;
        
        return $a > 0 && $b < 10 ? 'valid' : 'invalid';
    }

    public function negatedTernaryCondition(): string
    {
        $value = 5;
        
        return !($value > 0) ? 'not positive' : 'positive';
    }

    public function comparisonBeforeTernary(): string
    {
        $value = 5;
        
        return $value > $value + 1 ? 'impossible' : 'possible';
    }

    public function booleanOperatorBeforeParentheses(): bool
    {
        $a = true;
        $b = false;
        
        return $a && ($b);
    }

    public function mixedOperatorGroups(): int
    {
        return 2 * (3 + 4);
    }

    public function booleanOperatorAroundArithmetic(): bool
    {
        $a = 5;
        
        return $a > 0 && (5 + 10 > 10);
    }

    public function minusBeforeParentheses(): int
    {
        return 5 - (3 + 2);
    }

    public function orderOfOperations(): int
    {
        return (2 + 3) * 4;
    }

    public function complexLogicalCondition(): bool
    {
        $a = 1;
        $b = 2;
        $c = 3;
        
        return ($a > 0 && $b < 10) || $c === 5;
    }

    public function methodChaining(): string
    {
        return (new DateTime())->format('Y-m-d');
    }

    public function negationWithComplexCondition(): bool
    {
        $condition = true;
        
        return !($condition && true);
    }

    public function necessaryParenthesesForPrecedence(): int
    {
        return (1 + 2) * 3;
    }

    public function necessaryParenthesesForTypeCast(): string
    {
        return (string) 42;
    }

    public function necessaryParenthesesForInstanceOf(): bool
    {
        $object = new stdClass();
        
        return $object instanceof stdClass;
    }

    public function necessaryParenthesesForYield(): Generator
    {
        yield 1 + 2;
    }

    public function edgeCaseWithMixedParentheses(): int
    {
        return (2 + 3) * (4 + 5);
    }

}
