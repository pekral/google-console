<?php

declare(strict_types = 1);

/**
 * Example: list all sites in Search Console.
 * Test domain: pekral.cz
 *
 * Run: GOOGLE_CREDENTIALS_PATH=/path/to/credentials.json php examples/list-sites.php
 */

require __DIR__ . '/bootstrap.php';

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

$input = new ArrayInput([
    '--credentials' => $credentials,
    'command' => 'pekral:google-sites-list',
]);

$application->run($input, new ConsoleOutput());
