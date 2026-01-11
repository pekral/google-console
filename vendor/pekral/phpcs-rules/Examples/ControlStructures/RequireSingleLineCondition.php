<?php

declare(strict_types = 1);

namespace Example\ControlStructures;

final class RequireSingleLineCondition
{

    public function exampleWithSimpleCondition(int $value): string
    {
        // Correct - simple condition on single line
        if ($value > 0) {
            return 'positive';
        }

        return 'negative';
    }

    public function exampleWithLongSimpleCondition(int $value): string
    {
        // INCORRECT - simple condition split across multiple lines (violates RequireSingleLineCondition)
        if ($value > 0) {
            return 'positive';
        }

        return 'negative';
    }

    public function exampleWithComplexCondition(int $value, string $type): string
    {
        // Correct - complex condition with boolean operators can be multi-line
        if ($value > 100 && $type === 'premium' && $this->isUserActive()) {
            return 'premium user';
        }

        return 'regular user';
    }

    public function exampleWithWhileLoop(int $max): void
    {
        // Correct - simple while condition on single line
        while ($max > 0) {
            $max -= 1;
        }
    }

    public function exampleWithLongWhileCondition(int $max): void
    {
        // INCORRECT - simple while condition split across multiple lines (violates RequireSingleLineCondition)
        while ($max > 0) {
            $max -= 1;
        }
    }

    public function exampleWithDoWhileLoop(int $max): void
    {
        // Correct - simple do-while condition on single line
        do {
            $max -= 1;
        } while ($max > 0);
    }

    public function exampleWithLongDoWhileCondition(int $max): void
    {
        // INCORRECT - simple do-while condition split across multiple lines (violates RequireSingleLineCondition)
        do {
            $max -= 1;
        } while ($max > 0);
    }

    public function exampleWithElseIf(int $value): string
    {
        // Correct - simple elseif conditions on single lines
        if ($value > 100) {
            return 'high';
        }

        if ($value > 50) {
            return 'medium';
        }

        if ($value > 0) {
            return 'low';
        }

        return 'zero';
    }

    public function exampleWithLongElseIfCondition(int $value): string
    {
        // INCORRECT - simple elseif condition split across multiple lines (violates RequireSingleLineCondition)
        if ($value > 100) {
            return 'high';
        }

        if ($value > 50) {
            return 'medium';
        }

        if ($value > 0) {
            return 'low';
        }

        return 'zero';
    }

    private function isUserActive(): bool
    {
        return true;
    }

}
