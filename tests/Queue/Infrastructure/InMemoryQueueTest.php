<?php

namespace Liuggio\Fastest\Queue\Infrastructure;

use Liuggio\Fastest\Queue\TestsQueue;

class InMemoryQueueTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function shouldPopAndPushASuite(): void
    {
        $queue = new InMemoryQueue();
        $tests = new TestsQueue(['a', 'b']);
        $queue->push($tests);
        $this->assertEquals('b', $queue->pop());
        $this->assertEquals('a', $queue->pop());
        $this->assertTrue($queue->isEmpty());
    }

    /**
     * @test
     */
    public function shouldPopAndShiftASuite(): void
    {
        $queue = new InMemoryQueue();
        $tests = new TestsQueue(['a', 'b']);
        $queue->push($tests);
        $this->assertEquals('a', $queue->shift());
        $this->assertEquals('b', $queue->shift());
        $this->assertTrue($queue->isEmpty());
    }
}
