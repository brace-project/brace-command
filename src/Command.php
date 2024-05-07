<?php

namespace Brace\Command;

use Brace\Core\BraceApp;

class Command
{
    protected $commands = [];

    protected $globalArguments = [];

    public function __construct(
        protected BraceApp $app
    ){
        $this->addCommand("help", fn() => $this->printHelp(), "Print Help");
        $this->addCommand("scheduler", fn() => PredefinedCommands::Scheduler($app, $this), "Run the scheduler");
    }

    /**
     * Add a command to be run via brace shell
     *
     * brace <commandName>
     *
     * @param string $commandName
     * @param callable $fn
     * @param string $desc,
     * @param CliBoolArgument[]|CliValueArgument[]
     * @return void
     */
    public function addCommand(string $commandName, callable|array $fn, string $desc = "<no description>", array $arguments = [])
    {
        if (isset ($this->commands[$commandName]))
            throw new \InvalidArgumentException("Command '$commandName' is already defined");
        // Allow _-. in Names
        if ( ! ctype_alnum(str_replace(["_", "-", "."], '', $commandName)))
            throw new \InvalidArgumentException("Invalid Command name '$commandName' must be alphanumeric");
        $this->commands[$commandName] = [
            "desc" => $desc,
            "arguments" => $arguments,
            "fn" => $fn
        ];
    }

    /**
     * Add a global argument
     *
     * If parameter 2 is set, the function will be called with the parsed value before the command is executed.
     *
     * @param CliArgumentInterface $argument
     * @param \Closure|null $fn function($parsedValue, BraceApp $app) : void
     */
    public function addGlobalArgument(CliArgumentInterface $argument, \Closure $fn=null)
    {
        $this->globalArguments[] = ["argument" => $argument, "fn" => $fn];
    }

    public function __getGlobalArguments() : array
    {
        return $this->globalArguments;
    }
    /**
     * Add a Class and parse Arguments of Methods
     *
     * @param class-string $classString
     * @return void
     */
    public function addClass (string $classString) {

        $reflection = new \ReflectionClass($classString);
        foreach ($reflection->getMethods() as $method) {
            $mAttr = $method->getAttributes(CliCmd::class);
            if (count($mAttr) === 0)
                continue;
            /* @var $cmd CliCmd */
            $cmd = $mAttr[0]->newInstance();

            $arguments = [];
            foreach ($method->getAttributes() as $attribute) {
                $att = $attribute->newInstance();
                if ( ! $att instanceof CliArgumentInterface)
                    continue;
                $arguments[] = $att;
            }
            $this->addCommand($cmd->name, [$classString, $method->getName()], $cmd->desc, $arguments);
        }


    }


    public function printHelp()
    {
        echo "brace command line utility" . PHP_EOL;
        echo "Usage:" . PHP_EOL. PHP_EOL;
        echo "   brace [global arguments] <command> [arguments]:" . PHP_EOL;
        echo  PHP_EOL;
        echo "Global Arguments:" . PHP_EOL . PHP_EOL;
        // Print alll global arguments
        foreach ($this->globalArguments as $argument) {
            $argument = $argument["argument"];
            if ($argument instanceof CliValueArgument)
                echo "    " . str_pad($argument->name . " <val>", 25, " ") . "{$argument->desc}" . PHP_EOL;
            else
                echo "    " . str_pad($argument->name . "", 25, " ") . "{$argument->desc}" . PHP_EOL;
        }

        echo "" . PHP_EOL;
        echo "Commands:" . PHP_EOL . PHP_EOL;

        foreach ($this->commands as $cmdName => $command) {
            echo "  " . str_pad($cmdName . "", 26, " ") . " {$command["desc"]}" . PHP_EOL;
            foreach ($command["arguments"] as $argument) {
                /* @var CliValueArgument|CliBoolArgument $argument */
                if ($argument instanceof CliValueArgument)
                    echo "    " . str_pad($argument->name . " <val>", 25, " ") . "{$argument->desc}" . PHP_EOL;
                else
                    echo "    " . str_pad($argument->name . "", 25, " ") . "{$argument->desc}" . PHP_EOL;
            }
            echo  PHP_EOL;
        }

    }

    /**
     * Run the command from within the webapp
     *
     * @param string $commandName
     * @param array $argv
     * @param bool $returnOutput
     * @return string|null
     * @throws \Phore\Di\Container\DiUnresolvableInternalException
     */
    public function runCommand(string $commandName, array $argv=[], bool $returnOutput=false) : string|null
    {


        if ( ! isset($this->commands[$commandName]))
            throw new \InvalidArgumentException("No command with commandName '$commandName' defined");

        try {
            if ($returnOutput)
                ob_start();

            $callback = $this->commands[$commandName]["fn"];
            if (is_array($callback)) {
                $obj = phore_di_instantiate($callback[0], $this->app);
                $callback = \Closure::fromCallable([$obj, $callback[1]]);
            }

            $paramArgs = [];
            foreach ($this->commands[$commandName]["arguments"] as $argument) {
                if ( ! $argument instanceof CliArgumentInterface)
                    throw new \InvalidArgumentException("Invalid argument: Must be instance of CliArgumentInterface");
                $paramArgs[$argument->name] = $argument->parseVal($argv);
            }

            phore_di_call($callback, $this->app, [
                "argv" => $argv,
                "arguments" => $paramArgs
            ]);

            if ($returnOutput)
                return ob_get_clean();
        } catch (\Exception $e) {
            if ($returnOutput)
                ob_get_clean();
            throw $e;
        } catch (\Error $e) {
            if ($returnOutput)
                ob_get_clean();
            throw $e;
        }
        return null;
    }



    protected $interval = [];

    /**
     * Schedule the command for daily execution on 00:00:00
     * Will run fist after given interval
     *
     * brace scheduler must be executed
     *
     * Parameter 1 might be specified in cron format
     * <minutes> <hour> <dayofmonth> <dayofweek> <month>
     *
     * Example
     *   '* * * * *' Will run every minute
     *   '5 * * * *' Will run each our at 5 Minutes past
     *
     *
     * @param string $commandName
     * @param bool $resumeOnError
     * @return void
     */
    public function addInterval(int|string $interval, string $commandName, array $argv=[], bool $resumeOnError = false)
    {
        if (is_string($interval))
            $interval = new CronFmt($interval);

        if ( ! isset($this->commands[$commandName]))
            throw new \InvalidArgumentException("No command with commandName '$commandName' defined");
        $this->interval[] = [
            "lastRun" => time(),
            "interval" => $interval,
            "commandName" => $commandName,
            "argv" => $argv,
            "resumeOnError" => $resumeOnError
        ];
    }

}
