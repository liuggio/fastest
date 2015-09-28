<?php

namespace Liuggio\Fastest\Event;

use Liuggio\Fastest\Process\Process;
use Symfony\Component\EventDispatcher\Event;

final class ProcessGeneratedBufferEvent extends Event
{
    /**
     * @var Process
     */
    private $process;

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

    /**
     * @return int
     */
    public function getIncrementalOutput()
    {
        return $this->process->getIncrementalOutput();
    }

    /**
     * @throws LogicException in case the output has been disabled
     * @throws LogicException In case the process is not started
     *
     * @return string
     */
    public function getIncrementalErrorOutput()
    {
        return $this->process->getIncrementalErrorOutput();
    }
}
