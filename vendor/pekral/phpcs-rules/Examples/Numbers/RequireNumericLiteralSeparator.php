<?php

declare(strict_types = 1);

namespace Example\Numbers;

final class RequireNumericLiteralSeparator
{

    public const int LARGE_NUMBER = 10_000;
    public const float PRECISE_VALUE = 123.456_7;

    public function correctShortNumbers(): void
    {
        echo 1;
        echo 12;
        echo 123;
        echo 1.5;
        echo 12.34;
        echo 123.456;
    }

    public function correctLongNumbers(): void
    {
        echo 1_000;
        echo 10_000;
        echo 100_000;
        echo 1_000_000;
        echo 10_000_000;
    }

    public function correctLongDecimals(): void
    {
        echo 123.456_7;
        echo 123.456_78;
        echo 123.456_789;
        echo 1.123_456_789;
    }

    public function correctMixedLongNumbers(): void
    {
        echo 1_000.5;
        echo 123.456_7;
        echo 1_000.456_7;
        echo 10_000.123_456;
    }

    public function correctScientificWithLongBase(): void
    {
        echo 1_234e3;
        echo 12_345e10;
        echo 123.456_7e-3;
    }

    public function correctEdgeCases(): void
    {
        echo 999;
        echo 999.999;
        echo 0;
        echo -123;
    }

    public function correctEdgeCasesWithSeparators(): void
    {
        echo 1_000;
        echo 123.1_000;
        echo -1_000;
        echo -123.456_7;
    }

    public function correctInArrays(): void
    {
        echo [1_000, 2_000, 3_000];
        echo [
            'decimal' => 123.456_7,
            'large' => 10_000,
        ];
        echo 1_000 + 2_000 + 3_000;
    }

    public function correctInFunctions(): int
    {
        $this->processNumber(1_000);
        $this->processDecimal(123.456_7);

        return 10_000;
    }

    public function correctWithExistingSeparators(): void
    {
        echo 1_000;
        echo 10_000;
        echo 100_000;
        echo 1_000_000;
        echo 123.456_7;
        echo 1_000.456_7;
    }

    public function correctBinaryAndHex(): void
    {
        echo 0b10101010;
        echo 0x1F2E3D4C;
    }

    private function processNumber(int $number): void
    {
        echo $number;
    }

    private function processDecimal(float $decimal): void
    {
        echo $decimal;
    }

}