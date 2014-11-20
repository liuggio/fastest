<?php

namespace Liuggio\Fastest\Queue;


class ReadFromInputAndPushIntoTheQueueTest extends \PHPUnit_Framework_TestCase {

    /**
     * @test
     */
    public function shouldPushIntoTheQueueTheXMLFile()
    {
        $assertion = new TestsQueue(array('tests/Fastest/folderA', 'tests/Fastest/folderB'));

        $queue = $this->getMock('\Liuggio\Fastest\Queue\QueueInterface');
        $queue->expects($this->once())
            ->method('push')
            ->with($assertion);

        $factory = $this->getMockBuilder('\Liuggio\Fastest\Queue\QueueFactoryInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $factory
            ->expects($this->once())
            ->method('create')
            ->willReturn($queue);

        $reader = new ReadFromInputAndPushIntoTheQueue($factory);

        $ret = $reader->execute(__DIR__.'/Fixture/phpunit.xml.dist', true);

        $this->assertEquals($queue, $ret);
    }
}
 