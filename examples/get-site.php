<?php

declare(strict_types = 1);

/**
 * Example: get details for a specific site from Search Console.
 * Test domain: pekral.cz
 *
 * Run: GOOGLE_CREDENTIALS_PATH=/path/to/credentials.json php examples/get-site.php
 */

require __DIR__ . '/bootstrap.php';

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

$input = new ArrayInput([
    '--credentials' => $credentials,
    'command' => 'pekral:google-site-get',
    'site-url' => 'sc-domain:pekral.cz',
]);

$application->run($input, new ConsoleOutput());
