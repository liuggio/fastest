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
        $cmd = $this->replaceParameters($this->commandToExecuteTemplate, $suite, $currentProcessorNumber, $currentProcessCounter);
        $arrayEnv = $this->envCommandCreator->execute($currentProcessorNumber, $this->maxParallelProcessesToExecute, $suite, $currentProcessCounter, $isFirstOnThread);

        return $this->createProcess($cmd, $arrayEnv);
    }

    public function createAProcessForACustomCommand($execute, $currentProcessorNumber, $currentProcessCounter, $isFirstOnThread = false)
    {
        $cmd = $this->replaceParameters($execute, '', $currentProcessorNumber, $currentProcessCounter);
        $arrayEnv = $this->envCommandCreator->execute($currentProcessorNumber, $this->maxParallelProcessesToExecute, $execute, $currentProcessCounter, $isFirstOnThread);

        return $this->createProcess($cmd, $arrayEnv);
    }

    private function replaceParameters($cmd, $suite, $processNumber, $currentProcessCounter)
    {
        $commandToExecute = str_replace('{}', $suite, $cmd);
        $commandToExecute = str_replace('{p}', $processNumber, $commandToExecute);
        $commandToExecute = str_replace('{n}', $currentProcessCounter, $commandToExecute);

        return $commandToExecute;
    }

    private function createProcess($executeCommand, $arrayEnv)
    {
        $arrayEnv[] = 'PATH='.$_SERVER['PATH'];
        $process = new Process($executeCommand, null, $arrayEnv);

        $process->setTimeout(null);
        // compatibility to SF 2.2
        if (method_exists($process, 'setIdleTimeout')) {
            $process->setIdleTimeout(null);
        }

        return $process;
    }
}
