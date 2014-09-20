<?php

namespace Liuggio\Fastest\Queue\Infrastructure;

class InMemoryQueueFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldCreateAQueue()
    {
        $msqQueueFactory = new InMemoryQueueFactory();

        $queue = $msqQueueFactory->create();

        $this->assertInstanceOf('\Liuggio\Fastest\Queue\Infrastructure\InMemoryQueue', $queue);
    }
} 