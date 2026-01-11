<?php

declare(strict_types = 1);

namespace Example\Whitespace;

use stdClass;

final class ObjectOperatorIndent
{

    public function example(): void
    {
        $this->processData()
            ->validateData()
            ->formatOutput();
            
        $user = $this->getUser();
        $user->profile->settings->enabled = true;
    }
    
    private function processData(): self
    {
        return $this;
    }
    
    private function validateData(): self
    {
        return $this;
    }
    
    private function formatOutput(): void
    {
        echo 'Formatted output';
    }
    
    private function getUser(): stdClass
    {
        $user = new stdClass();
        $user->profile = new stdClass();
        $user->profile->settings = new stdClass();

        return $user;
    }

}
