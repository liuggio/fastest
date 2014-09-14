<?php

namespace Liuggio\Fastest\Queue;

class PopATestSuite
{
    protected $queue;

    function __construct(PopQueueInterface $queue)
    {
        $this->queue = $queue;
    }

    /**
     * @return TestSuite
     */
    public function execute()
    {
        return $this->queue->pop();
    }
} 