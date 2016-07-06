<?php

namespace Liuggio\Fastest\Queue;


class ReadFromInputAndPushIntoTheQueueTest extends \PHPUnit_Framework_TestCase {

    /**
     * @test
     */
    public function shouldPushIntoTheQueueTheXMLFile()
    {
        $directory = __DIR__ . '/Infrastructure/';
        $files = array('InMemoryQueueFactoryTest.php', 'InMemoryQueueTest.php');
        $assertion = new TestsQueue();

        foreach ($files as $file) {
            $assertion->add($directory . $file);
        }

        $queue = $this->createMock('\Liuggio\Fastest\Queue\QueueInterface');
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
