<?php

namespace Liuggio\Fastest\Process;

use Liuggio\Fastest\Channel;
use Liuggio\Fastest\InputLine;

class ProcessEnvironment
{
    const ENV_TEST_CHANNEL = 'ENV_TEST_CHANNEL';
    const ENV_TEST_CHANNEL_READABLE = 'ENV_TEST_CHANNEL_READABLE';
    const ENV_TEST_CHANNELS_NUMBER = 'ENV_TEST_CHANNELS_NUMBER';
    const ENV_TEST_ARGUMENT = 'ENV_TEST_ARGUMENT';
    const ENV_TEST_INCREMENTAL_NUMBER = 'ENV_TEST_INC_NUMBER';
    const ENV_TEST_IS_FIRST_ON_CHANNEL = 'ENV_TEST_IS_FIRST_ON_CHANNEL';

    /** @var Channel */
    private $channel;
    /** @var int */
    private $channelsNumber;
    /** @var InputLine */
    private $inputLine;
    /** @var int */
    private $incrementNumber;

    /**
     * @param Channel $channel
     * @param $channelsNumber
     * @param InputLine $inputLine
     * @param $incrementNumber
     */
    public function __construct(Channel $channel, InputLine $inputLine, $incrementNumber)
    {
        $this->channel = $channel;
        $this->inputLine = $inputLine;
        $this->incrementNumber = $incrementNumber;
    }

    public function exportToEnvsArray()
    {
        return array(
            self::ENV_TEST_CHANNEL.'='.$this->channel->getId(),
            self::ENV_TEST_CHANNEL_READABLE.'='.$this->getReadableChannel(),
            self::ENV_TEST_CHANNELS_NUMBER.'='.$this->getChannelsNumber(),
            self::ENV_TEST_ARGUMENT.'='.$this->getInputLine(),
            self::ENV_TEST_INCREMENTAL_NUMBER.'='.$this->getIncrementalNumber(),
            self::ENV_TEST_IS_FIRST_ON_CHANNEL.'='.(int) $this->isTheFirstCommandOnChannel(),
        );
    }

    /**
     * @return Channel
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @return Channel
     */
    public function getChannelId()
    {
        return $this->channel->getId();
    }

    /**
     * @return InputLine
     */
    public function getInputLine()
    {
        return $this->inputLine;
    }

    /**
     * @return string
     */
    public function getReadableChannel()
    {
        return 'test_'.(int) $this->channel->getId();
    }

    /**
     * @return int
     */
    public function getChannelsNumber()
    {
        return $this->channel->getChannelsNumber();
    }

    /**
     * @return int
     */
    public function getIncrementalNumber()
    {
        return $this->incrementNumber;
    }

    /**
     * @return bool
     */
    public function isTheFirstCommandOnChannel()
    {
        return ($this->channel->getAssignedProcessesCounter() == 1);
    }
}
