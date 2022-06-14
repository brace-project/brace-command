<?php

namespace Brace\Command;

#[\Attribute(\Attribute::TARGET_METHOD)]
class CliBoolArgument implements CliArgumentInterface
{
    public function __construct(
        public string $name,
        public string $desc = "<no description>"
    ){}

    public function parseVal(array &$argv): null|bool|array|string
    {
        foreach ($argv as $index => $arg) {
            if ($arg === $this->name) {
                unset ($argv[$index]);
                $argv = array_values($argv);
                return true;
            }
        }
        return false;
    }
}
