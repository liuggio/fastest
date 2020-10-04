<?php

namespace Liuggio\Fastest\Queue\Infrastructure;

use Liuggio\Fastest\Queue\QueueFactoryInterface;

class InMemoryQueueFactory implements QueueFactoryInterface
{
    /**
     * @var InMemoryQueue|null
     */
    private static $queue = null;

    public function create(): InMemoryQueue
    {
        if (null === self::$queue) {
            self::$queue = new InMemoryQueue();
        }

        return self::$queue;
    }
}
