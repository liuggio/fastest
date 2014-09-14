<?php

namespace Liuggio\Fastest\Queue\Infrastructure;

use Liuggio\Fastest\Queue\TestSuite;

class InMemoryQueueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldPopAndPushASuite()
    {
        $queue =  new InMemoryQueue();
        $testSuite = new TestSuite('a');
        $queue->push($testSuite);

        $this->assertEquals($testSuite, $queue->pop());
    }
} 