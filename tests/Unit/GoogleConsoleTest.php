<?php

declare(strict_types = 1);

use Pekral\GoogleConsole\GoogleConsole;

describe(GoogleConsole::class, function (): void {
    it('greets with name', function (): void {
        $instance = new GoogleConsole();

        expect($instance->greet('World'))->toBe('Hello, World!');
    });
});
