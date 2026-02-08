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
 * Optional: --mode=strict (default) or --mode=best-effort (allows heuristic INDEXED when data is inconclusive).
 *
 * Test domain: pekral.cz
 *
 * Run:
 *   GOOGLE_CREDENTIALS_PATH=/path/to/credentials.json php examples/inspect-url.php
 *
 * With best-effort mode:
 *   GOOGLE_CREDENTIALS_PATH=/path/to/credentials.json php examples/inspect-url.php --mode=best-effort
 *
 * JSON output (includes indexingCheckResult when present):
 *   GOOGLE_CREDENTIALS_PATH=/path/to/credentials.json php examples/inspect-url.php --json
 */

require __DIR__ . '/bootstrap.php';

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

$options = [
    '--credentials' => $credentials,
    'command' => 'pekral:google-url-inspect',
    'inspection-url' => 'https://pekral.cz/',
    'site-url' => 'sc-domain:pekral.cz',
];

$argv ??= [];

for ($i = 1; $i < count($argv); $i++) {
    $arg = $argv[$i];

    if ($arg === '--json' || $arg === '-j') {
        $options['--json'] = true;

        continue;
    }

    if (str_starts_with($arg, '--mode=')) {
        $options['--mode'] = substr($arg, 7);

        continue;
    }

    if ($arg !== '--mode' || !isset($argv[$i + 1])) {
        continue;
    }

    $options['--mode'] = $argv[$i + 1];
    $i++;
}

$input = new ArrayInput($options);
$application->run($input, new ConsoleOutput());
