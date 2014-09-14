<?php

namespace Liuggio\Fastest\Queue;

use Liuggio\Fastest\Queue\PopATestSuite;

class PopATestSuiteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldPushATestInAQueue()
    {
        $repositoryMock = $this->getMock('Liuggio\Fastest\Queue\PopQueueInterface');
        $repositoryMock
            ->expects($this->once())
            ->method('pop');

        $q = new PopATestSuite($repositoryMock);
        $q->execute();
    }
}