#!/usr/bin/env php
<?php

declare(strict_types = 1);

use Pekral\PhpcsRulesBuild\Command\UnusedSniffCommand;
use Symfony\Component\Console\Application;

require __DIR__ . '/vendor/autoload.php';


$application = new Application();
$application->add(new UnusedSniffCommand());
$application->run();