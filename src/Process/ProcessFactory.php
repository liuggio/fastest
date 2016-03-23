<?php

namespace Liuggio\Fastest\Process;

use Symfony\Component\Process\Process;

class ProcessFactory
{
    const DEFAULT_COMMAND_TO_EXECUTE_TPL = 'bin/phpunit {}';

    private $envCommandCreator;
    private $commandToExecuteTemplate;
    private $maxParallelProcessesToExecute;

    public function __construct($maxParallelProcessesToExecute, $commandToExecuteTemplate = null, EnvCommandCreator $envCommandCreator = null)
    {
        if (null === $envCommandCreator) {
            $envCommandCreator = new EnvCommandCreator();
        }
        if (null === $commandToExecuteTemplate || empty($commandToExecuteTemplate)) {
            $commandToExecuteTemplate = self::DEFAULT_COMMAND_TO_EXECUTE_TPL;
        }
        $this->maxParallelProcessesToExecute = $maxParallelProcessesToExecute;
        $this->envCommandCreator = $envCommandCreator;
        $this->commandToExecuteTemplate = $commandToExecuteTemplate;
    }

    public function createAProcess($suite, $currentProcessorNumber, $currentProcessCounter, $isFirstOnThread = false)
    {
        $cmd = $this->replaceParameters($this->commandToExecuteTemplate, $suite, $currentProcessorNumber);

        // inject enviroment variables
        $this->envCommandCreator->execute(
            $currentProcessorNumber,
            $this->maxParallelProcessesToExecute,
            $suite,
            $currentProcessCounter,
            $isFirstOnThread
        );

        return $this->createProcess($cmd);
    }

    public function createAProcessForACustomCommand($execute, $currentProcessorNumber, $currentProcessCounter, $isFirstOnThread = false)
    {
        $cmd = $this->replaceParameters($execute, '', $currentProcessorNumber);

        // inject enviroment variables
        $this->envCommandCreator->execute(
            $currentProcessorNumber,
            $this->maxParallelProcessesToExecute,
            $execute,
            $currentProcessCounter,
            $isFirstOnThread
        );

        return $this->createProcess($cmd);
    }

    private function replaceParameters($cmd, $suite, $processNumber)
    {
        $commandToExecute = str_replace('{}', $suite, $cmd);
        $commandToExecute = str_replace('{p}', $processNumber, $commandToExecute);

        return $commandToExecute;
    }

    private function createProcess($executeCommand)
    {
        $process = new Process(
            $executeCommand,
            null,
            null    // when passed null, process component should pick up current enviroment variables
        );

        $process->setTimeout(null);
        // compatibility to SF 2.2
        if (method_exists($process, 'setIdleTimeout')) {
            $process->setIdleTimeout(null);
        }

        return $process;
    }
}
