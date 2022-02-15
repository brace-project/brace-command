<?php

namespace Brace\Command;

use Brace\Core\BraceApp;

class PredefinedCommands extends Command
{
    public static function PrintHelp() {
        echo "\nBrace command tool";
        echo "\nUsage:";
        echo "\n    {$GLOBALS["argv"][0]} <command>";
        echo "\n";
        exit(1);
    }


    public static function Scheduler(BraceApp $app, Command $command)
    {
        echo "Running brace-scheduler...";
        while(true) {;
            sleep(1);
            foreach ($command->interval as $key => $interval) {
                if (time() < $interval["lastRun"] + $interval["interval"])
                    continue;

                $command->interval[$key]["lastRun"] = time();

                echo "\n[brace scheduler][" . date("Y-m-d H:i:s") . "] Running commandName '{$interval["commandName"]}'...";
                try {
                    $command->runCommand($interval["commandName"], $interval["argv"]);
                } catch (\Exception $e) {
                    echo "[ERR: Exception: " . $e->getMessage() . "]";
                    if ( ! $interval["resumeOnError"])
                        throw $e;
                } catch (\Error $e) {
                    echo "[ERR: Error: " . $e->getMessage() . "]";
                    if ( ! $interval["resumeOnError"])
                        throw $e;
                }
                echo "[OK]";
            }
        }
    }

}
