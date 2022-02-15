<?php

namespace Brace\Command;

class CliDispatcher extends Command
{


    public static function run(array $argv, int $argc)
    {
        $command = CommandModule::$commandInstance;
        if ($command === null)
            throw new \InvalidArgumentException("Module CommandModule from library 'brace/command' is not part of app. Run addModule() to add it.");

        array_shift($argv);
        $cmd = array_shift($argv);
        if ($cmd === null)
            $cmd = "help";

        if ( ! isset ($command->commands[$cmd])) {
            echo "Command undefined '$cmd'!\n";
            exit(255);
        }
        phore_di_call($command->commands[$cmd], $command->app, [
            "argv" => $argv
        ]);
        echo "\n";
    }
}
