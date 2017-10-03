<?php

namespace Liuggio\Fastest\Queue\Infrastructure;

use Liuggio\Fastest\Queue\QueueInterface;
use Liuggio\Fastest\Queue\TestsQueue;

class InMemoryQueue implements QueueInterface
{
    private $queue;

    public function __construct()
    {
        $this->queue = [];
    }

    public function pop()
    {
        return array_pop($this->queue);
    }

    public function push(TestsQueue $testSuite)
    {
        $this->queue = array_merge($this->queue, $testSuite->toArray());

        return (count($this->queue)>0);
    }

    public function getNumberOfPushedMessage()
    {
        return count($this->queue);
    }

    public function close()
    {
        $this->queue = null;
    }

    public function count()
    {
        if (null !== $this->queue) {
            return count($this->queue);
        }

        return 0;
    }

    public function isEmpty()
    {
        return ($this->count() == 0);
    }
}
