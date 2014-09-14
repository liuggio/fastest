<?php

namespace Liuggio\Fastest\Queue;

use Liuggio\Fastest\Queue\PushTestSuites;
use Liuggio\Fastest\Queue\TestSuite;

class PushTestSuitesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldPushATestInAQueue()
    {
        $repositoryMock = $this->getMock('Liuggio\Fastest\Queue\PushQueueInterface');
        $repositoryMock
            ->expects($this->once())
            ->method('push');

        $testSuite = new TestSuite('a');

        $q = new PushTestSuites($repositoryMock);
        $q->execute($testSuite);
    }
}