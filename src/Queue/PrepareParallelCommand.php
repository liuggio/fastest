<?php

namespace Liuggio\Fastest\Queue;


class PrepareParallelCommand
{
    const DEFAULT_PARALLELCOMMAND = 'parallel_test';
    private $parallelCommand;
    private $scriptName;

    function __construct($scriptName = null, $parallelCommand = null)
    {
        $this->parallelCommand = $parallelCommand;
        $this->scriptName = $scriptName;
        if (null === $this->parallelCommand) {
            $this->parallelCommand = self::DEFAULT_PARALLELCOMMAND;
        }
        if (null === $this->scriptName) {
            $this->scriptName = SCRIPT_NAME;
        }
    }

    public function execute($subProcess = null, $numberOfProcesses = null, $logDir = '')
    {
        $processOption = '';
        if (null !== $numberOfProcesses && (int)$numberOfProcesses>0) {
            $processOption = '-n '.$numberOfProcesses.'';
        }

        $subProcess = $this->prepareCommand($subProcess, $logDir);
        $command = sprintf('%s %s -e "%s"', $this->parallelCommand, $processOption, $subProcess);

        return $command;
    }

    private function prepareCommand($execute = null, $logDir = '')
    {
        if (null !== $logDir && !empty($logDir)) {
            $logDir = '--log-dir=\''.$logDir.'\'';
        }

        if (null !== $execute) {
            $execute = '\''.$execute.'\'';
        }

        return sprintf('php %s consume %s -l %s', $this->scriptName, $execute, $logDir);
    }
} 