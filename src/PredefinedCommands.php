<?php

namespace Brace\Command;

class PredefinedCommands
{
    public static function PrintHelp() {
        echo "\nBrace command tool";
        echo "\nUsage:";
        echo "\n    {$GLOBALS["argv"][0]} <command>";
        echo "\n";
        exit(1);
    }
}
