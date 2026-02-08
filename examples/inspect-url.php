<?php

declare(strict_types = 1);

/**
 * Example: inspect a URL for indexing status and mobile usability.
 *
 * Runs the pekral:google-url-inspect command, which outputs:
 * - Indexing status (verdict, coverage state, indexing state, robots.txt, page fetch, last crawl)
 * - Business output model (when available): primary status (INDEXED | NOT_INDEXED | UNKNOWN),
 *   confidence (high | medium | low), reason_codes, checked_at, source_type
 * - Canonical URLs (Google, user)
 * - Mobile usability (mobile-friendly verdict and issues)
 *
 * Test domain: pekral.cz
 *
 * Run:
 *   GOOGLE_CREDENTIALS_PATH=/path/to/credentials.json php examples/inspect-url.php
 *
 * JSON output (includes indexingCheckResult when present):
 *   GOOGLE_CREDENTIALS_PATH=/path/to/credentials.json php examples/inspect-url.php --json
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
