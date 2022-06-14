<?php

namespace Brace\Command;

#[\Attribute(\Attribute::TARGET_METHOD)]
class CliValueArgument implements CliArgumentInterface
{
    public function __construct(
        public string $name,
        public bool $array = false,
        public string $desc = "<no description>"
    ){}


    public function parseVal(array &$argv): bool|string
    {

        $val = null;
        if ($this->array)
            $val = [];
        foreach ($argv as $index => $arg) {
            if ($arg === $this->name) {
                unset ($argv[$index]);
                if ( ! isset ($argv[$index+1]))
                    throw new \InvalidArgumentException("Missing value for argument '$this->name'");

                if ($this->array) {
                    $val[] = $argv[$index+1];
                } else {
                    $val = $argv[$index+1];
                }
                unset ($argv[$index+1]);
                $argv = array_values($argv);
            }
        }
        return $val;
    }
}
