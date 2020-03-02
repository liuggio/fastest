<?php

namespace Liuggio\Fastest\Queue\Infrastructure;

use Liuggio\Fastest\Queue\Infrastructure\InMemoryQueue;
use PHPUnit\Framework\TestCase;

class InMemoryQueueFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function shouldCreateAQueue()
    {
        $msqQueueFactory = new InMemoryQueueFactory();

        $queue = $msqQueueFactory->create();

        $this->assertInstanceOf(InMemoryQueue::class, $queue);
    }
}
