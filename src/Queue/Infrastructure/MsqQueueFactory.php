<?php

namespace Liuggio\Fastest\Queue\Infrastructure;

use Liuggio\Fastest\Queue\QueueFactoryInterface;

class MsqQueueFactory implements QueueFactoryInterface
{
    private static $queuePort = null;
    private static $consumerFIFO = null;
    private static $producerFIFO = null;

    public function __construct($queuePort)
    {
        self::$queuePort = $queuePort;
    }

    public function createForProducer()
    {
        if (null === self::$producerFIFO) {
            self::$producerFIFO = MsgQueue::createMsgQueueOnAnEmptyPort(self::$queuePort);
            self::$queuePort = self::$producerFIFO->getQueuePort();
        }

        return self::$producerFIFO;
    }

    public function createForConsumer()
    {
        if (null === self::$consumerFIFO) {
            self::$consumerFIFO = MsgQueue::createMsgQueueOnThisPort(self::$queuePort);
            self::$queuePort = self::$consumerFIFO->getQueuePort();
        }

        return self::$consumerFIFO;
    }

    /**
     * @return mixed
     */
    public static function getQueuePort()
    {
        return self::$queuePort;
    }
}
