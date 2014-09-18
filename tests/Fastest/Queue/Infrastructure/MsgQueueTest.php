<?php

namespace Liuggio\Fastest\Queue\Infrastructure;

use Liuggio\Fastest\Queue\TestsQueue;

class MsqQueueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @functional
     */
    public function shouldWriteAndReadFromTheFIFO()
    {
        $queueName = rand(100379,1000379)+((int) getenv("ENV_TEST_NUMBER"));
        if (msg_queue_exists($queueName)) {
            $res = msg_get_queue($queueName);
            return msg_remove_queue($res);
        }

        $queue = new MsgQueue($queueName);

        $tests =  new TestsQueue(array('a','b'));
        $queue->push($tests);
        $this->assertEquals('a', (string) $queue->pop());
        $this->assertEquals('b', (string) $queue->pop());
    }
} 