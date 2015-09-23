<?php

namespace Liuggio\Fastest\Consumer;

use Liuggio\Fastest\Channel;
use Liuggio\Fastest\CommandLine;
use Liuggio\Fastest\Event\ChannelIsWaitingEvent;

class ConsumerListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function onChannelWaitABeforeCommandLineShouldExecuteANewProcess()
    {
        $this->markTestIncomplete();

        $channel = Channel::createAWaiting(3, 5);
        $channelAssertion = $channel->assignToABeforeCommandLine();

        $queue = $this->getMock('\Liuggio\Fastest\Queue\QueueInterface');
        $queue->expects($this->never())
            ->method('dequeue');

        $createAndWait = $this->getMockBuilder('\Liuggio\Fastest\Process\CreateStartAndWaitAProcess')
            ->disableOriginalConstructor()
            ->getMock();

        $createAndWait->expects($this->once())
            ->method('createAndStartAProcess')
            ->with($this->equalTo($channelAssertion));

        $consumer = new ConsumerListener($queue, $createAndWait, CommandLine::fromString("echo 'a'"));
        $consumer->onChannelIsWaitingACommandLine(new ChannelIsWaitingEvent($channel));
    }
}
