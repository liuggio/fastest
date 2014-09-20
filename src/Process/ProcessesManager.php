<?php

namespace Liuggio\Fastest\Process;

use Liuggio\Fastest\Queue\QueueInterface;
use Symfony\Component\Process\Process;

class ProcessesManager
{
    private $processFactory;
    private $maxNumberOfParallelProcess;
    private $beforeCommand;

    public function __construct(ProcessFactory $processFactory = null, $maxNumberOfParallelProcess, $beforeCommand = null)
    {
        if (null === $processFactory) {
            $processFactory = new ProcessFactory($maxNumberOfParallelProcess);
        }

        $this->processFactory = $processFactory;
        $this->maxNumberOfParallelProcess = $maxNumberOfParallelProcess;
        $this->beforeCommand = $beforeCommand;
    }

    public function assertNProcessRunning(QueueInterface &$queue, Processes &$processes = null)
    {
        if (null === $processes) {

            $toBeCreated = range(1, $this->maxNumberOfParallelProcess);
            $processes =  new Processes(array());

            if (false !== $this->beforeCommand && null !== $this->beforeCommand) {
                return $this->createProcessesForTheBeforeCommand($toBeCreated, $processes);
            }

        } else {
            $toBeCreated = $processes->getIndexesOfCompleted();
        }

        if (count($toBeCreated) == 0) {
            usleep(100);
            return true;
        }

        foreach ($toBeCreated as $key) {
            if ($queue->isEmpty()) {
                return false;
            }

            $process = $this->processFactory->createAProcess($queue->pop(), $key);
            $processes->add($key, $process);

            $processes->start($key);
        }

        return true;
    }

    private function createProcessesForTheBeforeCommand($range, Processes &$processes = null)
    {
        foreach ($range as $key) {
            $process = $this->processFactory->createAProcessForACustomCommand($this->beforeCommand, $key);
            $processes->add($key, $process);
            $processes->start($key);
        }

        return true;
    }
}
