<?php

namespace Liuggio\Fastest\Queue;


class PrepareSingleCommand
{
    const DEFAULT_COMMAND = 'php %s consume "%s" -l';
    private $scriptName;

    function __construct($scriptName = null)
    {
        $this->scriptName = $scriptName;

        if (null === $this->scriptName) {
            $this->scriptName = SCRIPT_NAME;
        }
    }

    public function execute($subProcess = null)
    {
        $command = sprintf(self::DEFAULT_COMMAND, $this->scriptName, $subProcess);

        return $command;
    }
} 