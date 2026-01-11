<?php

declare(strict_types = 1);

namespace Example\Strings;

final class DisallowVariableParsing
{

    private string $name = 'John';
    private int $age = 25;
    private array $data = ['key' => 'value'];

    public function correctStrings(): void
    {
        echo 'Hello World';
        echo "Simple string";
        echo 'String with quotes: "nested"';
        echo "String with apostrophe: John's book";
        
        echo 'Hello ' . $this->name . '!';
        echo 'Age: ' . $this->age;
        echo 'Data: ' . json_encode($this->data);
        
        echo <<<'EOT'
            This is a heredoc string
            without any deprecated ${...} syntax.
            It's completely safe.
            EOT;
    }

    public function correctDollarCurlySyntax(): void
    {
        $name = $this->name;
        $age = $this->age;
        $data = $this->data;
        
        echo 'Hello ' . $name . '!';
        echo 'Age: ' . $age;
        echo 'Data: ' . $data['key'];
        
        echo <<<EOT
            Hello {$name}! 
            Age: {$age}
            EOT;
    }

    public function correctCurlyDollarSyntax(): void
    {
        $name = $this->name;
        $age = $this->age;
        $data = $this->data;
        
        echo "Hello {$name}!";
        echo "Age: {$age}";
        echo "Data: {$data['key']}";
        
        echo <<<EOT
            Hello {$name}! 
            Age: {$age}
            EOT;
    }

    public function correctSimpleSyntax(): void
    {
        $name = $this->name;
        $age = $this->age;
        $data = $this->data;
        
        echo "Hello $name!";
        echo "Age: $age";
        echo "Data: $data";
        
        echo <<<EOT
            Hello $name! 
            Age: $age
            EOT;
    }

    public function correctAlternatives(): void
    {
        echo 'Hello ' . $this->name . '!';
        echo 'Age: ' . $this->age;
        echo 'Data: ' . $this->data['key'];
        
        echo sprintf('Hello %s!', $this->name);
        echo sprintf('Age: %d', $this->age);
        echo sprintf('Data: %s', $this->data['key']);
        
        echo <<<EOT
            Hello {$this->name}! 
            Age: {$this->age}
            EOT;
    }

    public function correctComplexStrings(): void
    {
        echo 'User: ' . $this->name . ' (' . $this->age . ' years old)';
        echo 'Data: ' . json_encode($this->data, JSON_PRETTY_PRINT);
        
        echo 'This is a very long string ' . 'that spans multiple lines ' . 'without using deprecated ${...} syntax.';
    }

    public function correctMixedSyntax(): void
    {
        $name = $this->name;
        $age = $this->age;
        $data = $this->data;
        
        echo 'Hello ' . $name . ', age ' . $age . ', data ' . $data;
        
        echo <<<EOT
            Hello {$name}, age {$age}, data {$data}
            EOT;
    }

    public function correctEdgeCases(): void
    {
        echo 'Price: $19.99';
        echo "Cost: \$25.50";
        echo 'Currency: $';
        
        echo 'Text with {brackets}';
        echo 'Function: {closure}';
    }

    public function correctInDifferentContexts(): void
    {
        $name = $this->name;
        $age = $this->age;
        $data = $this->data;
        
        $message = 'Hello ' . $name . '!';
        $greeting = "Welcome {$name}";
        $info = "Age: $age";
        
        echo $message;
        echo $greeting;
        echo $info;
        
        echo "Data: {$data['key']}";
    }

    public function correctStringOperations(): void
    {
        $message = 'Hello ' . $this->name . '!';
        $greeting = sprintf('Welcome %s', $this->name);
        $info = "Age: {$this->age}";
        
        echo $message;
        echo $greeting;
        echo $info;
    }

}
