<?php

namespace Liuggio\Fastest\Process;

use Liuggio\Fastest\Queue\QueueInterface;
use Symfony\Component\Process\Process;

class ProcessesManager
{
    private $processFactory;
    private $maxNumberOfParallelProcesses;
    private $beforeCommand;
    private $processCounter;
    private $isFirstForItsChannel;

    public function __construct(ProcessFactory $processFactory = null, $maxNumberOfParallelProcesses, $beforeCommand = null)
    {
        if (null === $processFactory) {
            $processFactory = new ProcessFactory($maxNumberOfParallelProcesses);
        }

        $this->processFactory = $processFactory;
        $this->maxNumberOfParallelProcesseses = $maxNumberOfParallelProcesses;
        $this->beforeCommand = $beforeCommand;
        $this->processCounter = 0;
        $this->isFirstForItsChannel = array();
    }

    public function getNumberOfProcessExecutedByTheBeforeCommand()
    {
        if (false !== $this->beforeCommand && null !== $this->beforeCommand) {
            return $this->maxNumberOfParallelProcesseses;
        }

        return 0;
    }

    public function assertNProcessRunning(QueueInterface &$queue, Processes &$processes = null)
    {
        $parallelProcesses = max(1, min($queue->count(), $this->maxNumberOfParallelProcesses));

        if (null === $processes) {

            $channelsEmpty = range(1, $parallelProcesses);
            $processes =  new Processes(array());

            if (false !== $this->beforeCommand && null !== $this->beforeCommand) {
                return $this->createProcessesForTheBeforeCommand($channelsEmpty, $processes);
            }

        } else {
            $channelsEmpty = $processes->getIndexesOfCompletedChannel();

        }

        if (count($channelsEmpty) == 0) {
            usleep(100);

            return true;
        }

        foreach ($channelsEmpty as $currentChannel) {
            if ($queue->isEmpty()) {
                return false;
            }

            $currentProcessNumber = $this->getCurrentProcessCounter();
            $this->incrementForThisChannel($currentChannel);
            $process = $this->processFactory->createAProcess($queue->pop(), $currentChannel, $currentProcessNumber, $this->isFirstForThisChannel($currentChannel));
           // $processes->add($currentChannel, $process);

         //   $processes->start($currentChannel);
        }

        return true;
    }

    private function createProcessesForTheBeforeCommand($range, Processes &$processes = null)
    {
        foreach ($range as $currentChannel) {
            $currentProcessNumber = $this->getCurrentProcessCounter();
            $this->incrementForThisChannel($currentChannel);
            $process = $this->processFactory->createAProcessForACustomCommand($this->beforeCommand, $currentChannel, $currentProcessNumber, $this->isFirstForThisChannel($currentChannel));
            $processes->add($currentChannel, $process);
            $processes->start($currentChannel);
        }

        return true;
    }

    private function getCurrentProcessCounter()
    {
        return ++$this->processCounter;
    }

    private function incrementForThisChannel($Channel)
    {
        if (isset($this->isFirstForItsChannel[$Channel])) {
            $this->isFirstForItsChannel[$Channel]++;

            return;
        }

        $this->isFirstForItsChannel[$Channel] = 1;
    }

    private function isFirstForThisChannel($Channel)
    {
        return (isset($this->isFirstForItsChannel[$Channel]) && $this->isFirstForItsChannel[$Channel] == 1);
    }
}
