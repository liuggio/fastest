<?php

namespace Liuggio\Fastest\Queue\Infrastructure;

class MsqQueueFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldCreateAQueueForWriting()
    {
        $queueName = 100379;
        $msqQueueFactory = new MsqQueueFactory($queueName);

        $queue = $msqQueueFactory->createForProducer();

        $this->assertInstanceOf('\Liuggio\Fastest\Queue\Infrastructure\MsgQueue', $queue);
    }
} 