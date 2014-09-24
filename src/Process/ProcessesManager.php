<?php

namespace Liuggio\Fastest\Process;

use Liuggio\Fastest\Queue\QueueInterface;
use Symfony\Component\Process\Process;

class ProcessesManager
{
    private $processFactory;
    private $maxNumberOfParallelProcess;
    private $beforeCommand;
    private $processCounter;
    private $isFirstForItsThread;

    public function __construct(ProcessFactory $processFactory = null, $maxNumberOfParallelProcess, $beforeCommand = null)
    {
        if (null === $processFactory) {
            $processFactory = new ProcessFactory($maxNumberOfParallelProcess);
        }

        $this->processFactory = $processFactory;
        $this->maxNumberOfParallelProcess = $maxNumberOfParallelProcess;
        $this->beforeCommand = $beforeCommand;
        $this->processCounter = 0;
        $this->isFirstForItsThread = array();
    }

    public function getNumberOfProcessExecutedByTheBeforeCommand()
    {
        if (false !== $this->beforeCommand && null !== $this->beforeCommand) {
            return $this->maxNumberOfParallelProcess;
        }

        return 0;
    }

    public function assertNProcessRunning(QueueInterface &$queue, Processes &$processes = null)
    {
        $parallelProcess = max(1, min($queue->count(), $this->maxNumberOfParallelProcess));

        if (null === $processes) {

            $toBeCreated = range(1, $parallelProcess);
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

            $currentThread = $this->getCurrentProcessCounter();
            $this->incrementForThisThread($key);
            $process = $this->processFactory->createAProcess($queue->pop(), $key, $currentThread, $this->isFirstForThisThread($key));
            $processes->add($key, $process);

            $processes->start($key);
        }

        return true;
    }

    private function createProcessesForTheBeforeCommand($range, Processes &$processes = null)
    {
        foreach ($range as $key) {
            $currentThread = $this->getCurrentProcessCounter();
            //$this->incrementForThisThread($key);
            $process = $this->processFactory->createAProcessForACustomCommand($this->beforeCommand, $key, $currentThread, $this->isFirstForThisThread($key));
            $processes->add($key, $process);
            $processes->start($key);
        }

        return true;
    }

    private function getCurrentProcessCounter()
    {
        ++$this->processCounter;

        return $this->processCounter;
    }

    private function incrementForThisThread($thread)
    {
        if (isset($this->isFirstForItsThread[$thread])) {
            return $this->isFirstForItsThread[$thread]++;
        }

        return $this->isFirstForItsThread[$thread] = 1;
    }

    private function isFirstForThisThread($thread)
    {
        return (!isset($this->isFirstForItsThread[$thread]) || $this->isFirstForItsThread[$thread] == 1);
    }
}
