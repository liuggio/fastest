<?php

namespace Liuggio\Fastest\Process;

use Symfony\Component\Process\Process;

class CreateNProcesses
{
    private $processorCounter;
    private $envCommandCreator;

    public function __construct(ProcessorCounter $processCounter = null, EnvCommandCreator $envCommandCreator = null)
    {
        if (null === $processCounter) {
            $processCounter = new ProcessorCounter();
        }
        if (null == $envCommandCreator) {
            $envCommandCreator = new EnvCommandCreator();
        }

        $this->processorCounter = $processCounter;
        $this->envCommandCreator = $envCommandCreator;
    }

    public function execute($executeCommand, $forceProcessNumber = null)
    {
        $numberOfCPU = $this->processorCounter->execute();

        if (null !== $forceProcessNumber || (int) $forceProcessNumber > 0) {
            $numberOfCPU = (int) $forceProcessNumber;
        }

        for ($int = 1; $int <= $numberOfCPU;$int++) {
            $arrayEnv = $this->envCommandCreator->execute($int, $numberOfCPU);
            $processes[] = $this->createProcess($executeCommand, $arrayEnv);
        }

        return new Processes($processes);
    }

    private function createProcess($executeCommand, $arrayEnv)
    {
        $process = new Process($executeCommand, null, $arrayEnv);
        $process->setTimeout(null);
        $process->setIdleTimeout(null);

        return $process;
    }
}
