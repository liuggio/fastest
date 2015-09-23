<?php

namespace Liuggio\Fastest\Event;

use Liuggio\Fastest\Process\Process;
use Symfony\Component\EventDispatcher\Event;

final class ProcessGeneratedBufferEvent extends Event
{
    private $process;

    public function __construct(Process $process)
    {
        $this->process = $process;
    }

    public function getProcess()
    {
        return $this->process;
    }

    public function getIncrementalOutput()
    {
        return $this->process->getIncrementalOutput();
    }

    public function getIncrementalErrorOutput()
    {
        return $this->process->getIncrementalErrorOutput();
    }
}
