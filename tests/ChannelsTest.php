<?php

namespace Liuggio\Fastest;

class ChannelsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldGetAllTheEmptyChannels()
    {
        $channels = Channels::createWaiting(5);
        $this->assertCount(5, $channels->getWaitingChannels());
        $this->assertCount(0, $channels->getAssignedChannels());
    }

    /**
     * @test
     */
    public function shouldGetAllThe4EmptyChannels()
    {
        $channels = Channels::createWaiting(5);
        $process = $this->getMockBuilder('\Liuggio\Fastest\Process\Process')
            ->disableOriginalConstructor()
            ->getMock();

        $channels->assignAProcess(Channel::createAWaiting(1, 1), $process);

        $this->assertCount(4, $channels->getWaitingChannels());
        $this->assertCount(1, $channels->getAssignedChannels());
    }

    /**
     * @test
     */
    public function shouldGetAllTheAssignedChannels()
    {
        $channels = Channels::createWaiting(5);

        foreach ($channels->getWaitingChannels() as $channel) {
            $process = $this->getMockBuilder('\Liuggio\Fastest\Process\Process')
                ->disableOriginalConstructor()
                ->getMock();
            $channels->assignAProcess($channel, $process);
        }

        $this->assertCount(5, $channels->getAssignedChannels());
        $this->assertCount(0, $channels->getWaitingChannels());
    }
}
