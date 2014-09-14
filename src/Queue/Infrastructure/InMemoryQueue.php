<?php

namespace Liuggio\Fastest\Queue\Infrastructure;

use Liuggio\Fastest\Queue\PopQueueInterface;
use Liuggio\Fastest\Queue\PushQueueInterface;
use Liuggio\Fastest\Queue\TestSuite;

class InMemoryQueue implements PopQueueInterface, PushQueueInterface
{
    private $queue;

    public function __construct()
    {
        $this->queue = array();
    }

    /**
     * @return TestSuite
     */
    public function pop()
    {
        return array_pop($this->queue);
    }

    /**
     * @param TestSuite $testSuite
     *
     * @return bool
     */
    public function push(TestSuite $testSuite)
    {
        return (array_push($this->queue, $testSuite)>0);
    }

} 