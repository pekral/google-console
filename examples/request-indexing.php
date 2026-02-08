<?php

declare(strict_types = 1);

/**
 * Example: request indexing for a URL (or removal with --delete).
 * Test domain: pekral.cz
 *
 * Run: GOOGLE_CREDENTIALS_PATH=/path/to/credentials.json php examples/request-indexing.php
 */

require __DIR__ . '/bootstrap.php';

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

$input = new ArrayInput([
    '--credentials' => $credentials,
    'command' => 'pekral:google-request-indexing',
    'url' => 'https://pekral.cz/novy-clanek',
]);

$application->run($input, new ConsoleOutput());
