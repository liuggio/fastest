<?php

namespace Liuggio\Fastest\Process;

use Liuggio\Fastest\Queue\QueueInterface;

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
        $this->maxNumberOfParallelProcesses = $maxNumberOfParallelProcesses;
        $this->beforeCommand = $beforeCommand;
        $this->processCounter = 0;
        $this->isFirstForItsChannel = [];
    }

    public function assertNProcessRunning(QueueInterface &$queue, Processes &$processes = null)
    {
        $parallelProcesses = max(1, min($queue->count(), $this->maxNumberOfParallelProcesses));

        if (null === $processes) {
            $channelsEmpty = range(1, $parallelProcesses);
            $processes = new Processes([]);

            if (false !== $this->beforeCommand && null !== $this->beforeCommand) {
                return $this->createProcessesForTheBeforeCommand($channelsEmpty, $processes);
            }
        } else {
            $channelsEmpty = $processes->getIndexesOfCompletedChannel();
        }

        if (0 == count($channelsEmpty)) {
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
            $processes->add($currentChannel, $process);
            $processes->start($currentChannel);
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
            $processes->wait(function () use ($processes) {
                if ($processes->getExitCode()) {
                    $errorOutput = $processes->getErrorOutput();
                    $output = current($errorOutput);
                    $name = key($errorOutput);

                    throw new \Exception(sprintf('Before command "%s" failed with message: "%s"', $name, $output));
                }
            }, false);
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
            ++$this->isFirstForItsChannel[$Channel];

            return;
        }

        $this->isFirstForItsChannel[$Channel] = 1;
    }

    private function isFirstForThisChannel($Channel)
    {
        return isset($this->isFirstForItsChannel[$Channel]) && 1 == $this->isFirstForItsChannel[$Channel];
    }
}
