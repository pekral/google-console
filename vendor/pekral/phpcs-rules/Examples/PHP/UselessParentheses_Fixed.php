<?php

declare(strict_types = 1);

namespace Example\PHP;

use DateTime;

final class UselessParentheses
{

    public function correctOrderOfOperations(): int
    {
        return (2 + 3) * 4;
    }

    public function correctComplexConditions(int $a, int $b, int $c): bool
    {
        return ($a > 0 && $b < 10) || $c === 5;
    }

    public function correctOperatorPrecedence(): int
    {
        return 2 + 3 * (4 + 5);
    }

    public function correctNegation(bool $condition): bool
    {
        return !($condition && true);
    }

    public function correctMethodChaining(): string
    {
        return (new DateTime())->format('Y-m-d');
    }

    public function correctArrayAccess(): mixed
    {
        $array = ['key' => ['nested' => 'value']];

        return $array['key']['nested'];
    }

}
