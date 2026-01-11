<?php

declare(strict_types = 1);

namespace Example\Whitespace;

final class OperatorSpacing
{

    public function example(): void
    {
        $a = 5;
        $b = 3;
        
        $sum = $a + $b;
        $difference = $a - $b;
        $product = $a * $b;
        $quotient = $a / $b;
        
        $isGreater = $a > $b;
        $isLess = $a < $b;
        $isEqual = $a === $b;
        $isNotEqual = $a !== $b;
        
        $logicalAnd = $a > 0 && $b > 0;
        $logicalOr = $a > 0 || $b > 0;
        
        $this->outputResults($sum, $difference, $product, $quotient);
        $this->outputComparisons($isGreater, $isLess, $isEqual, $isNotEqual);
        $this->outputLogical($logicalAnd, $logicalOr);
    }
    
    private function outputResults(int $sum, int $difference, int $product, float $quotient): void
    {
        echo "Sum: $sum, Difference: $difference, Product: $product, Quotient: $quotient";
    }
    
    private function outputComparisons(bool $isGreater, bool $isLess, bool $isEqual, bool $isNotEqual): void
    {
        echo "Greater: " . ($isGreater ? 'true' : 'false');
        echo "Less: " . ($isLess ? 'true' : 'false');
        echo "Equal: " . ($isEqual ? 'true' : 'false');
        echo "Not Equal: " . ($isNotEqual ? 'true' : 'false');
    }
    
    private function outputLogical(bool $logicalAnd, bool $logicalOr): void
    {
        echo "Logical AND: " . ($logicalAnd ? 'true' : 'false');
        echo "Logical OR: " . ($logicalOr ? 'true' : 'false');
    }

}
