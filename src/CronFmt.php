<?php

namespace Brace\Command;

class CronFmt
{
    public function __construct(
        private string $cronFmt
    )
    {
        if (! preg_match("/^[0-9,*]+ [0-9,*]+ [0-9,*]+ [0-9,*]+ [0-9,*]+$/", $cronFmt))
            throw new \InvalidArgumentException("Invalid cron time specifier: '$cronFmt'");
    }

    public function matches (int $now = null) : bool
    {
        if($now === null)
            $now = time();

        $cronArr = explode(" ", $this->cronFmt);
        $compare = [(int)date("i", $now), (int)date("G", $now), (int)date("j", $now), (int)date("w", $now), (int)date("n", $now)];

        $match = 0;
        foreach ($cronArr as $key => $val) {
            if ($val === "*") {
                $match++;
                continue;
            }

            foreach (explode(",", $val) as $curVal) {
                if (((int)$curVal) === $compare[$key]) {
                    $match++;
                    break;
                }
            }
        }
        return $match === 5;
    }
}
