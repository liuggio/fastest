<?php

namespace Liuggio\Fastest\Process;


use Liuggio\Fastest\Queue\TestSuite;
use Symfony\Component\Process\Process;

class ProcessesManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldCreateNProcessRunningGivingNullAsProcesses()
    {
        $queue = $this->getMock('Liuggio\Fastest\Queue\QueueInterface');

        $command = 0;
        $factory = $this->getMockBuilder('Liuggio\Fastest\Process\ProcessFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $factory->expects($this->exactly(1))
            ->method('createAProcessForACustomCommand')
            ->willReturn(new Process('echo ',rand()));

        $manager = new ProcessesManager($factory, 1, 'echo "ciao"');

        $processes = null;

        $manager->assertNProcessRunning($queue, $processes);

        $this->assertTrue($manager->assertNProcessRunning($queue, $processes));
        $this->assertEquals(1, $processes->count());
    }

    /**
     * @test
     */
    public function shouldCreateNProcessRunningGivingProcesses()
    {
        $queue = $this->getMock('Liuggio\Fastest\Queue\QueueInterface');
        $queue->expects($this->exactly(2))
            ->method('isEmpty')
            ->willReturn(false);
        $queue->expects($this->exactly(2))
            ->method('pop')
            ->willReturn(new TestSuite('path'));

        $processes = $this->getMockBuilder('Liuggio\Fastest\Process\Processes')
            ->disableOriginalConstructor()
            ->getMock();

        $processes->expects($this->exactly(1))
            ->method('getIndexesOfCompleted')
            ->willReturn(range(1,2));


        $command = 0;
        $factory = $this->getMockBuilder('Liuggio\Fastest\Process\ProcessFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $factory->expects($this->exactly(2))
            ->method('createAProcess')
            ->willReturn(new Process('echo ',rand()));
        $factory->expects($this->exactly(0))
            ->method('createAProcessForACustomCommand')
            ->willReturn(new Process('echo ',rand()));

        $manager = new ProcessesManager($factory, 1);

        $this->assertTrue($manager->assertNProcessRunning($queue, $processes));
    }


}
 