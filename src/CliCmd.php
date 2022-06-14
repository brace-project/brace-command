<?php

namespace Brace\Command;

#[\Attribute(\Attribute::TARGET_METHOD)]
class CliCmd
{
    public function __construct(
        public string $name,
        public string $desc = "<no description>"
    ){}
}
