<?php

namespace Liuggio\Fastest\Queue\Infrastructure;

use Liuggio\Fastest\Queue\TestsQueue;
use Liuggio\Fastest\Queue\TestSuite;

class InMemoryQueueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldPopAndPushASuite()
    {
        $queue = new InMemoryQueue();
        $tests =  new TestsQueue(array('a','b'));
        $queue->push($tests);
        $this->assertEquals('b', $queue->pop());
        $this->assertEquals('a', $queue->pop());
    }
} 