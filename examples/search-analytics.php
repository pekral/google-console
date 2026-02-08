<?php

declare(strict_types = 1);

/**
 * Example: search analytics for a site (queries, pages, date).
 * Test domain: pekral.cz
 *
 * Run: GOOGLE_CREDENTIALS_PATH=/path/to/credentials.json php examples/search-analytics.php
 */

require __DIR__ . '/bootstrap.php';

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

$input = new ArrayInput([
    '--credentials' => $credentials,
    '--dimensions' => 'query',
    '--end-date' => new DateTimeImmutable()->format('Y-m-d'),
    '--row-limit' => '100',
    '--start-date' => new DateTimeImmutable('-30 days')->format('Y-m-d'),
    'command' => 'pekral:google-analytics-search',
    'site-url' => 'sc-domain:pekral.cz',
]);

$application->run($input, new ConsoleOutput());
