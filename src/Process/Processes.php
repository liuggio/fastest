<?php

namespace Liuggio\Fastest\Process;

use Symfony\Component\Process\Process;

class Processes
{
    private $processes;

    public function __construct(array $processes)
    {
        $this->processes = $processes;
    }

    public function start()
    {
        return array_walk($this->processes, function ($item, $key) {
            $item->start();
        });
    }

    public function stop()
    {
        return array_walk($this->processes, function ($item) {
            $item->stop();
        });
    }

    public function wait()
    {
        return array_walk($this->processes, function ($item, $key) {
            $item->wait();
        });
    }

    public function count()
    {
        return count($this->processes);
    }

    public function get($index)
    {
        return $this->processes[$index];
    }

    /**
     * @return array
     */
    public function getProcesses()
    {
        return $this->processes;
    }
}
