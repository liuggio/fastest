<?php

namespace Liuggio\Fastest\Queue\Infrastructure;

class InMemoryQueueFactoryTest extends \PHPUnit\Framework\TestCase
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
