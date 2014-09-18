<?php

namespace Liuggio\Fastest\Queue;


class ConsumerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldRetrieveATestSuiteFromTheQueue()
    {
        $testSuite = new TestSuite('a');

        $mockObject = $this->getMock('\Liuggio\Fastest\Queue\QueueInterface');
        $mockObject->expects($this->once())
            ->method('pop')
            ->willReturn($testSuite);

        $factory = $this->getMock('\Liuggio\Fastest\Queue\QueueFactoryInterface');
        $factory->expects($this->once())
            ->method('createForConsumer')
            ->willReturn($mockObject);


        $producer = new Consumer($factory);

        $this->assertEquals($testSuite, $producer->execute());
    }
}
 