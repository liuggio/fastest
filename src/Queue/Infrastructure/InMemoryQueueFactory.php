<?php

namespace Liuggio\Fastest\Queue\Infrastructure;

use Liuggio\Fastest\Queue\QueueFactoryInterface;

class InMemoryQueueFactory implements QueueFactoryInterface
{
    private static $queue = null;

    public function create()
    {
        if (null === self::$queue) {
            self::$queue = new InMemoryQueue();
        }

        return self::$queue;
    }
}
