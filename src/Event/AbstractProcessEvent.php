<?php

namespace Liuggio\Fastest\Event;

use Liuggio\Fastest\Process\Process;
use Symfony\Component\EventDispatcher\Event;

abstract class AbstractProcessEvent extends Event
{
    /**
     * @var Process
     */
    protected $process;

    /**
     * @param Process $process
     */
    public function __construct(Process $process)
    {
        $this->process = $process;
    }

    /**
     * @return Process
     */
    public function getProcess()
    {
        return $this->process;
    }
}
