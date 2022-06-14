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
        $this->commands["scheduler"] = fn() => PredefinedCommands::Scheduler($app, $this);
    }

    /**
     * Add a command to be run via brace shell
     *
     * brace <commandName>
     *
     * @param string $commandName
     * @param callable $fn
     * @return void
     */
    public function addCommand(string $commandName, callable $fn)
    {
        if (isset ($this->commands[$commandName]))
            throw new \InvalidArgumentException("Command '$commandName' is already defined");
        // Allow _-. in Names
        if ( ! ctype_alnum(str_replace(["_", "-", "."], '', $commandName)))
            throw new \InvalidArgumentException("Invalid Command name '$commandName' must be alphanumeric");
        $this->commands[$commandName] = $fn;
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

            phore_di_call($this->commands[$commandName], $this->app, [
                "argv" => $argv
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
