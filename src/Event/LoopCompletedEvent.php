<?php

namespace Liuggio\Fastest\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Stopwatch\StopwatchEvent;

class LoopCompletedEvent extends Event
{
    /**
     * @var StopwatchEvent
     */
    private $stopwatchEvent;

    /**
     * @var int
     */
    private $exitCode;

    /**
     * @param StopwatchEvent $stopwatchEvent
     * @param int            $exitCode
     */
    public function __construct(StopwatchEvent $stopwatchEvent, $exitCode)
    {
        $this->stopwatchEvent = $stopwatchEvent;
        $this->exitCode = (int) $exitCode;
    }

    /**
     * @return StopwatchEvent
     */
    public function getStopwatchEvent()
    {
        return $this->stopwatchEvent;
    }

    /**
     * @return int
     */
    public function getExitCode()
    {
        return $this->exitCode;
    }
}
