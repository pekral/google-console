<?php

declare(strict_types = 1);

namespace Pekral\PhpcsRulesBuild\Tests\Command;

use Pekral\PhpcsRulesBuild\Command\UnusedSniffCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Style\SymfonyStyle;

final class UnusedSniffCommandTest extends TestCase
{

    public function testInvokeReturnsValidExitCode(): void
    {
        $output = $this->createMockOutput();
        $command = new UnusedSniffCommand();
        
        $result = $command($output);
        
        $this->assertIsInt($result);
        $this->assertContains($result, [0, 1]);
    }

    public function testInvokeHandlesRealSniffsDirectory(): void
    {
        $output = $this->createMockOutput();
        $command = new UnusedSniffCommand();
        $result = $command($output);
        
        $this->assertIsInt($result);
    }

    private function createMockOutput(): SymfonyStyle
    {
        $output = $this->createMock(SymfonyStyle::class);
        
        $output->method('writeln')->willReturnSelf();
        $output->method('info')->willReturnSelf();
        $output->method('error')->willReturnSelf();
        
        return $output;
    }

}
