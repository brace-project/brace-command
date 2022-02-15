<?php

namespace Brace\Command;

use Brace\Core\BraceApp;

class Command
{
    protected $commands = [];

    public function __construct(
        protected BraceApp $app
    ){
        $this->commands["help"] = fn() => PredefinedCommands::PrintHelp();
    }

    public function addCommand(string $name, callable $fn)
    {
        if (isset ($this->commands[$name]))
            throw new \InvalidArgumentException("Command '$name' is already defined");
        if ( ! ctype_alnum($name))
            throw new \InvalidArgumentException("Invalid Command name '$name' must be alphanumeric");
        $this->commands[$name] = $fn;
    }

}
