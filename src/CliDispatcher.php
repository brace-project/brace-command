<?php

namespace Brace\Command;

use Brace\Core\AppLoader;

class CliDispatcher extends Command
{


    public static function run(array $argv, int $argc)
    {
        $app = AppLoader::loadApp();
        $command = CommandModule::$commandInstance;
        if ($command === null)
            throw new \InvalidArgumentException("Module CommandModule from library 'brace/command' is not part of app. Run addModule() to add it.");

        array_shift($argv);

        while (count($argv) > 0 && $arg = $argv[0]) {
            if (substr($arg, 0, 1) !== "-") {
                break;
            }

            echo "ARG: $arg\n";
            $found = false;
            foreach ($command->__getGlobalArguments() as $globalArgument) {
                $argument = $globalArgument["argument"];
                /* @var $argument CliArgumentInterface */
                if ($argument->getName() == $arg) {
                    $argValue = $argument->parseVal($argv);
                    if ($globalArgument["fn"] !== null) {
                        $globalArgument["fn"]($argValue, $app);
                    }
                    $found = true;
                    break;
                }
            }
            if ( ! $found) {
                echo "Unknown global argument '$arg'\n";
                exit(255);
            }
            continue;
        }

        $cmd = array_shift($argv);
        if ($cmd === null)
            $cmd = "help";

        if ( ! isset ($command->commands[$cmd])) {
            echo "Command undefined '$cmd'!\n";
            exit(255);
        }
        $command->runCommand($cmd, $argv);
        echo "\n";
    }
}
