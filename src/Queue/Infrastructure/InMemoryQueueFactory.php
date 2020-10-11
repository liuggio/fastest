<?php

namespace Liuggio\Fastest\Queue\Infrastructure;

use Liuggio\Fastest\Queue\QueueFactoryInterface;
use Liuggio\Fastest\Queue\QueueInterface;

class InMemoryQueueFactory implements QueueFactoryInterface
{
    /**
     * @var InMemoryQueue|null
     */
    private static $queue = null;

    public function create(): QueueInterface
    {
        if (null === self::$queue) {
            self::$queue = new InMemoryQueue();
        }

        return self::$queue;
    }
}
