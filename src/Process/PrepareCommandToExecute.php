<?php

namespace Liuggio\Fastest\Process;

class PrepareCommandToExecute
{
    const DEFAULT_COMMAND = 'php %s co:si "%s" "%s"';
    const DEFAULT_SUB_PROCESS = 'bin/phpunit {}';
    private $scriptName;

    public function __construct($scriptName = null)
    {
        $this->scriptName = $scriptName;

        if (null === $this->scriptName) {
            $this->scriptName = SCRIPT_NAME;
        }
    }

    public function execute($queueFilePath, $subProcess = null)
    {
        if (null == $subProcess || empty($subProcess)) {
            $subProcess = self::DEFAULT_SUB_PROCESS;
        }

        $command = sprintf(self::DEFAULT_COMMAND, $this->scriptName, $queueFilePath, $subProcess);

        return $command;
    }
}
