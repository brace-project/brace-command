<?php

namespace Brace\Command;


use Brace\Core\BraceApp;
use Brace\Core\BraceModule;
use Phore\Di\Container\Producer\DiService;
use Phore\Di\Container\Producer\DiValue;

class CommandModule implements BraceModule
{
    /**
     * @var Command
     */
    public static $commandInstance;

    public function register (BraceApp $app) {
        $app->define("command", new DiValue(self::$commandInstance = new Command($app)));
    }
}
