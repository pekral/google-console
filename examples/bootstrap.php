<?php

declare(strict_types = 1);

require __DIR__ . '/../vendor/autoload.php';

$credentials = getenv('GOOGLE_CREDENTIALS_PATH') ?: __DIR__ . '/../laserstorm-2d1c761d5a97.json';

if ($credentials === '' || !is_file($credentials)) {
    echo "Error: Credentials file not found.\n";
    echo "Set the GOOGLE_CREDENTIALS_PATH environment variable.\n";
    exit(1);
}
