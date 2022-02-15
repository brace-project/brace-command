<?php

namespace Brace\Command;

use Brace\Core\BraceModule;

class CommandModule implements BraceModule
{
    /**
     * @var Command
     */
    public static $commandInstance;

    public function register (BraceApp $app) {
        $app->define("command", self::$commandInstance = new Command($app));
    }
}
