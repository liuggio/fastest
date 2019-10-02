<?php

namespace Liuggio\Fastest\Process;

use Liuggio\Fastest\Queue\TestSuite;
use Symfony\Component\Process\Process;

class ProcessesManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function shouldCreateBeforeProcessesExecutingFactoryWithTheCorrectArguments()
    {
        $queue = $this->createMock('Liuggio\Fastest\Queue\QueueInterface');

        $factory = $this->getMockBuilder('Liuggio\Fastest\Process\ProcessFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $factory->expects($this->once())
            ->method('createAProcessForACustomCommand')
            ->with($this->anything(), $this->equalTo(1), $this->equalTo(1), $this->equalTo(true))
            ->willReturn(new Process('echo '.rand(), sys_get_temp_dir()));

        $manager = new ProcessesManager($factory, 1, 'echo "ciao"');

        $processes = null;
        $this->assertTrue($manager->assertNProcessRunning($queue, $processes));
    }

    /**
     * @test
     */
    public function shouldCreateProcessesWithoutBeforeProcessExecutingFactoryWithTheCorrectArguments()
    {
        $queue = $this->createMock('Liuggio\Fastest\Queue\QueueInterface');
        $queue->expects($this->once())
            ->method('isEmpty')
            ->willReturn(false);
        $queue->expects($this->once())
            ->method('shift')
            ->willReturn(new TestSuite('path'));

        $processes = $this->getMockBuilder('Liuggio\Fastest\Process\Processes')
            ->disableOriginalConstructor()
            ->getMock();

        $processes->expects($this->once())
            ->method('getIndexesOfCompletedChannel')
            ->willReturn([1]);

        $factory = $this->getMockBuilder('Liuggio\Fastest\Process\ProcessFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $factory->expects($this->exactly(1))
            ->method('createAProcess')
            ->with($this->anything(), $this->equalTo(1), $this->equalTo(1), $this->equalTo(true))
            ->willReturn(new Process('echo ', rand()));

        $manager = new ProcessesManager($factory, 1);

        $this->assertTrue($manager->assertNProcessRunning($queue, $processes));
    }

    /**
     * @test
     */
    public function shouldCreate6ProcessesGivingThemTheCorrectEnvParameters()
    {
        $queue = $this->createMock('Liuggio\Fastest\Queue\QueueInterface');
        $queue->expects($this->exactly(6))
            ->method('isEmpty')
            ->willReturn(false);
        $queue->expects($this->exactly(6))
            ->method('shift')
            ->willReturn(new TestSuite('path'));

        $processes = $this->getMockBuilder('Liuggio\Fastest\Process\Processes')
            ->disableOriginalConstructor()
            ->getMock();
        $processes->expects($this->any())
            ->method('getIndexesOfCompletedChannel')
            ->willReturn(range(1, 3));
        $processes->expects($this->any())
            ->method('add')
            ->willReturn(true);
        $processes->expects($this->any())
        ->method('start')
        ->willReturn(true);

        $factory = $this->getMockBuilder('Liuggio\Fastest\Process\ProcessFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $array = [
            [1, 1, true],
            [2, 2, true],
            [3, 3, true],
            [1, 4, false],
            [2, 5, false],
            [3, 6, false],
        ];

        foreach ($array as $at => $expectation) {
            $factory->expects($this->at($at))
                ->method('createAProcess')
                ->with($this->anything(), $this->equalTo($expectation[0]), $this->equalTo($expectation[1]), $this->equalTo($expectation[2]))
                ->willReturn(new Process('echo ', rand()));
        }

        $manager = new ProcessesManager($factory, 1);

        $this->assertTrue($manager->assertNProcessRunning($queue, $processes));
        $this->assertTrue($manager->assertNProcessRunning($queue, $processes));
    }
}
