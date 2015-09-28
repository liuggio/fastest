<?php

namespace Liuggio\Fastest\Event;

use Symfony\Component\EventDispatcher\Event;

class LoopStartedEvent extends Event
{
    /**
     * @var int
     */
    private $channelsNumber;

    /**
     * @param int $channelsNumber
     */
    public function __construct($channelsNumber)
    {
        $this->channelsNumber = $channelsNumber;
    }

    /**
     * @return int
     */
    public function getChannelsNumber()
    {
        return $this->channelsNumber;
    }
}
