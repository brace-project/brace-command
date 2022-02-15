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
        $compare = [(int)date("i"), (int)date("G"), (int)date("j"), (int)date("n"), (int)date("w")];

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
