<?php

namespace Liuggio\Fastest\Queue\Infrastructure;

use Liuggio\Fastest\Queue\TestSuite;

class PredisQueueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldPopAndPushASuite()
    {
        $queue =  new PredisQueue('Queue');
        $testSuite = new TestSuite('a');

        $queue->push($testSuite);

        $this->assertEquals($testSuite, $queue->pop());
    }
} 