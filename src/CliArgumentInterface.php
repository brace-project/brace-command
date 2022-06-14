<?php

namespace Brace\Command;

interface CliArgumentInterface
{

    public function parseVal(array &$argv) : bool|string;

}
