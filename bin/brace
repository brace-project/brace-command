#!/usr/bin/php
<?php
/**
 * Brace Command (brace/command)
 *
 * Cli to access methods via cli
 *
 *
 */

if (PHP_SAPI !== 'cli') {
    echo 'Warning: Application should be invoked via the CLI version of PHP, not the '.PHP_SAPI.' SAPI'.PHP_EOL;
}

if (file_exists(__DIR__ . "/../vendor/autoload.php")) {
    require __DIR__ . "/../vendor/autoload.php";
} else {
    require __DIR__ . "/../../../autoload.php";
}

\Brace\Command\CliDispatcher::run();

