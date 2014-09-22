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
        if (null == $envCommandCreator) {
            $envCommandCreator = new EnvCommandCreator();
        }
        if (null == $commandToExecuteTemplate || empty($commandToExecuteTemplate)) {
            $commandToExecuteTemplate = self::DEFAULT_COMMAND_TO_EXECUTE_TPL;
        }
        $this->maxParallelProcessesToExecute = $maxParallelProcessesToExecute;
        $this->envCommandCreator = $envCommandCreator;
        $this->commandToExecuteTemplate = $commandToExecuteTemplate;
    }

    public function createAProcess($suite, $currentProcessNumber)
    {
        $cmd = $this->replaceParameters($this->commandToExecuteTemplate, $suite, $currentProcessNumber);
        $arrayEnv = $this->envCommandCreator->execute($currentProcessNumber, $this->maxParallelProcessesToExecute, $suite);

        return $this->createProcess($cmd, $arrayEnv);
    }

    public function createAProcessForACustomCommand($execute, $currentProcessNumber)
    {
        $cmd = $this->replaceParameters($execute, '', $currentProcessNumber);
        $arrayEnv = $this->envCommandCreator->execute($currentProcessNumber, $this->maxParallelProcessesToExecute, $execute);

        return $this->createProcess($cmd, $arrayEnv);
    }

    private function replaceParameters($cmd, $suite, $processNumber)
    {
        $commandToExecute = str_replace('{}', $suite, $cmd);
        $commandToExecute = str_replace('{p}', $processNumber, $commandToExecute);

        return $commandToExecute;
    }

    private function createProcess($executeCommand, $arrayEnv)
    {
        $process = new Process($executeCommand, null, $arrayEnv);

        $process->setTimeout(null);
        // compatibility to SF 2.2
        if (method_exists($process,'setIdleTimeout')) {
            $process->setIdleTimeout(null);
        }


        return $process;
    }
}
