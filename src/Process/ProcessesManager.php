<?php

namespace Liuggio\Fastest\Process;

use Liuggio\Fastest\Queue\QueueInterface;

class ProcessesManager
{
    /**
     * @var ProcessFactory
     */
    private $processFactory;

    /**
     * @var int
     */
    private $maxNumberOfParallelProcesses;

    /**
     * @var string|null
     */
    private $beforeCommand;

    /**
     * @var int
     */
    private $processCounter;

    /**
     * @var int[]
     */
    private $isFirstForItsChannel;

    public function __construct(
        int $maxNumberOfParallelProcesses,
        ProcessFactory $processFactory = null,
        string $beforeCommand = null
    ) {
        if (null === $processFactory) {
            $processFactory = new ProcessFactory($maxNumberOfParallelProcesses);
        }

        $this->processFactory = $processFactory;
        $this->maxNumberOfParallelProcesses = $maxNumberOfParallelProcesses;
        $this->beforeCommand = $beforeCommand;
        $this->processCounter = 0;
        $this->isFirstForItsChannel = [];
    }

    public function assertNProcessRunning(QueueInterface &$queue, Processes &$processes = null): bool
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
            if (null === $process = $queue->shift()) {
                continue;
            }

            $process = $this->processFactory->createAProcess(
                $process->getTestPath(),
                $currentChannel,
                $currentProcessNumber,
                $this->isFirstForThisChannel($currentChannel)
            );
            $processes->add($currentChannel, $process);
            $processes->start($currentChannel);
        }

        return true;
    }

    /**
     * @param int[] $range
     * @param Processes $processes
     *
     * @return bool
     */
    private function createProcessesForTheBeforeCommand(array $range, Processes &$processes): bool
    {
        if (null === $beforeCommand = $this->beforeCommand) {
            return false;
        }

        foreach ($range as $currentChannel) {
            $currentProcessNumber = $this->getCurrentProcessCounter();
            $this->incrementForThisChannel($currentChannel);
            $process = $this->processFactory->createAProcessForACustomCommand(
                $beforeCommand,
                $currentChannel,
                $currentProcessNumber,
                $this->isFirstForThisChannel($currentChannel)
            );
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

    private function getCurrentProcessCounter(): int
    {
        return ++$this->processCounter;
    }

    private function incrementForThisChannel(int $channel): void
    {
        if (isset($this->isFirstForItsChannel[$channel])) {
            ++$this->isFirstForItsChannel[$channel];

            return;
        }

        $this->isFirstForItsChannel[$channel] = 1;
    }

    private function isFirstForThisChannel(int $channel): bool
    {
        return isset($this->isFirstForItsChannel[$channel]) && 1 == $this->isFirstForItsChannel[$channel];
    }
}
