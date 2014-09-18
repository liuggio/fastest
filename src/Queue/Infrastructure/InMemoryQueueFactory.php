<?php

namespace Liuggio\Fastest\Queue\Infrastructure;

use Liuggio\Fastest\Queue\QueueFactoryInterface;

class InMemoryQueueFactory implements QueueFactoryInterface
{
    private static $consumerFIFO = null;
    private static $producerFIFO = null;

    public function createForProducer()
    {
        if (null === self::$producerFIFO) {
            self::$producerFIFO = new InMemoryQueue();
        }

        return self::$producerFIFO;
    }

    public function createForConsumer()
    {
        if (null === self::$consumerFIFO) {
            self::$consumerFIFO = new InMemoryQueue();
        }

        return self::$consumerFIFO;
    }
}
