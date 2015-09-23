<?php

namespace Liuggio\Fastest;

use Liuggio\Fastest\Process\Process;

class Channels
{
    /** @var array */
    private $channels;

    private function __construct($channelsNumber)
    {
        for ($i = 0; $i < $channelsNumber; ++$i) {
            $channel = Channel::createAWaiting($i, $channelsNumber);
            $this->channels[$channel->getId()] = $channel;
        }
    }

    /**
     * Creates a bunch of waiting channels.
     *
     * @param $channelsNumber
     *
     * @return Channels
     */
    public static function createWaiting($channelsNumber)
    {
        return new self($channelsNumber);
    }

    /**
     * Assign a channel to a process.
     *
     * @param Channel $channel
     * @param Process $process
     */
    public function assignAProcess(Channel $channel, Process $process)
    {
        $this->channels[$channel->getId()] = $channel->assignToAProcess($process);
    }

    /**
     * Free a channel.
     *
     * @param Channel $channel
     */
    public function setEmpty(Channel $channel)
    {
        $this->channels[$channel->getId()] = $channel->setIsWaiting();
    }

    /**
     * Array of all the waiting channels.
     *
     * @return Channel[]
     */
    public function getWaitingChannels()
    {
        return array_values(array_filter($this->channels, function (Channel $channel) {
            return $channel->isWaiting();
        }));
    }

    /**
     * Array of all the assigned channels.
     *
     * @return Channel[]
     */
    public function getAssignedChannels()
    {
        return array_values(array_filter($this->channels, function (Channel $channel) {
            return !$channel->isWaiting();
        }));
    }
}
