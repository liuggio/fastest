<?php

namespace Liuggio\Fastest\Queue;


class PrepareParallelCommand
{
    const DEFAULT_PARALLELCOMMAND = 'parallel_test';
    private $parallelCommand;

    function __construct($parallelCommand = null)
    {
        $this->parallelCommand = $parallelCommand;

        if (null === $this->parallelCommand) {
            $this->parallelCommand = self::DEFAULT_PARALLELCOMMAND;
        }
    }

    public function execute($subProcess, $numberOfProcesses = null)
    {
        $processOption = '';
        if (null !== $numberOfProcesses && (int)$numberOfProcesses>0) {
            $processOption = '-n '.$numberOfProcesses.'';
        }

        $command = sprintf('%s %s -e "%s"', $this->parallelCommand, $processOption, $subProcess);

        return $command;
    }
} 