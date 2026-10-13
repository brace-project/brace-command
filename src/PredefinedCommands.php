<?php

namespace Brace\Command;

use Brace\Core\BraceApp;

class PredefinedCommands extends Command
{


    public static function Scheduler(BraceApp $app, Command $command)
    {
        echo "Running brace-scheduler..." . PHP_EOL;
        while(true) {;
            sleep(1);
            foreach ($command->interval as $key => $interval) {
                if ($interval["interval"] instanceof CronFmt) {
                    if (time() < $interval["lastRun"] + 60)
                        continue;
                    $matches = $interval["interval"]->matches();
                    echo "[brace scheduler][" . date("Y-m-d H:i:s") . "] Cron check: command=" . $interval["commandName"] . ", lastRun=" . date("Y-m-d H:i:s", $interval["lastRun"]) . ", matches=" . ($matches ? "yes" : "no") . PHP_EOL;
                    if ( ! $matches)
                        continue;
                } else {
                    if (time() < $interval["lastRun"] + $interval["interval"])
                        continue;
                }


                $command->interval[$key]["lastRun"] = time();

                $startedAt = microtime(true);
                echo "[brace scheduler][" . date("Y-m-d H:i:s") . "] START command=" . $interval["commandName"] . ", previousLastRun=" . date("Y-m-d H:i:s", $interval["lastRun"]) . PHP_EOL;
                try {
                    $command->runCommand($interval["commandName"], $interval["argv"]);
                } catch (\Throwable $e) {
                    echo "[brace scheduler][" . date("Y-m-d H:i:s") . "] ERROR command=" . $interval["commandName"] . ", duration=" . round(microtime(true) - $startedAt, 3) . "s, " . get_class($e) . ": " . $e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL;
                    if ( ! $interval["resumeOnError"])
                        throw $e;
                    continue;
                }
                echo "[brace scheduler][" . date("Y-m-d H:i:s") . "] END command=" . $interval["commandName"] . ", duration=" . round(microtime(true) - $startedAt, 3) . "s [OK]" . PHP_EOL;
            }
        }
    }

}
