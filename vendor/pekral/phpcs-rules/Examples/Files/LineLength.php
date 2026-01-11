<?php

declare(strict_types = 1);

namespace Example\Files;

final class LineLength
{

    public function exampleWithProperlySplitLine(): void
    {
        $message = 'This text is very long, but is properly split into multiple lines.';

        echo $message;
    }

    public function exampleWithLongMethodCall(): void
    {
        $result = $this->getSomeData()
            ->processData()
            ->validateData()
            ->getResult();

        echo $result;
    }

    private function getSomeData(): self
    {
        return $this;
    }

    private function processData(): self
    {
        return $this;
    }

    private function validateData(): self
    {
        return $this;
    }

    private function getResult(): string
    {
        return 'processed data';
    }

}