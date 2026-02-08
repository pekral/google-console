<?php

declare(strict_types = 1);

/**
 * Example: check URL indexing and mobile usability.
 * Test domain: pekral.cz
 *
 * Run: GOOGLE_CREDENTIALS_PATH=/path/to/credentials.json php examples/inspect-url.php
 */

require __DIR__ . '/bootstrap.php';

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

$input = new ArrayInput([
    '--credentials' => $credentials,
    'command' => 'pekral:google-url-inspect',
    'inspection-url' => 'https://pekral.cz/',
    'site-url' => 'sc-domain:pekral.cz',
]);

$application->run($input, new ConsoleOutput());
