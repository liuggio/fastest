<?php

namespace Liuggio\Fastest;

use Liuggio\Fastest\Process\Process;

class Channel
{
    /**
     * @var int
     */
    private $channelId;

    /**
     * @var int
     */
    private $assignedProcessesCounter;

    /**
     * @var int
     */
    private $channelsNumber;

    /**
     * @var Process
     */
    private $process;

    /**
     * @param int          $channelId
     * @param int          $channelsNumber
     * @param int          $commandsCounter
     * @param Process|null $process
     */
    private function __construct($channelId, $channelsNumber, $commandsCounter = 0, Process $process = null)
    {
        $this->channelId = $channelId;
        $this->channelsNumber = $channelsNumber;
        $this->assignedProcessesCounter = $commandsCounter;
        $this->process = $process;
    }

    /**
     * Creates a channel.
     *
     * @param int $id
     * @param int $channelsNumber
     *
     * @return Channel
     */
    public static function createAWaiting($id, $channelsNumber)
    {
        return new self($id, $channelsNumber, 0, null);
    }

    /**
     * Assigns a channel, incrementing the command line counter.
     *
     * @param Process $process
     *
     * @return Channel
     */
    public function assignToAProcess(Process $process)
    {
        return new self($this->getId(), $this->channelsNumber, 1 + $this->getAssignedProcessesCounter(), $process);
    }

    /**
     * The Channel is not assigned, and is waiting a Process to Run.
     *
     * @return Channel
     */
    public function setIsWaiting()
    {
        return new self($this->getId(), $this->channelsNumber, $this->getAssignedProcessesCounter(), null);
    }

    /**
     * True if the Channel is free to be assigned.
     *
     * @return bool
     */
    public function isWaiting()
    {
        return (null === $this->process);
    }

    /**
     * True if we can assign a BeforeCommandLine for this Channel,
     * and the before command is the first on channel.
     *
     * @return bool
     */
    public function isPossibleToAssignABeforeCommand()
    {
        return ($this->isWaiting() && $this->assignedProcessesCounter == 0);
    }

    /**
     * Get The Channel identifier.
     *
     * @return int
     */
    public function getId()
    {
        return $this->channelId;
    }

    /**
     * @return int
     */
    public function getChannelsNumber()
    {
        return $this->channelsNumber;
    }

    /**
     * @return Process
     */
    public function getProcess()
    {
        return $this->process;
    }

    /**
     * @return int
     */
    public function getAssignedProcessesCounter()
    {
        return $this->assignedProcessesCounter;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->channelId;
    }
}
