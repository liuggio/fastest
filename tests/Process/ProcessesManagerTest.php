<?php

namespace Liuggio\Fastest\Process;

use Liuggio\Fastest\Queue\TestSuite;
use Symfony\Component\Process\Process;
use PHPUnit\Framework\TestCase;

class ProcessesManagerTest extends TestCase
{
    /**
     * @test
     */
    public function shouldCreateBeforeProcessesExecutingFactoryWithTheCorrectArguments(): void
    {
        $queue = $this->createMock('Liuggio\Fastest\Queue\QueueInterface');

        $factory = $this->getMockBuilder('Liuggio\Fastest\Process\ProcessFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $factory->expects($this->once())
            ->method('createAProcessForACustomCommand')
            ->with($this->anything(), $this->equalTo(1), $this->equalTo(1), $this->equalTo(true))
            ->willReturn(new Process(['echo ', rand()], sys_get_temp_dir()));

        $manager = new ProcessesManager(1, $factory, 'echo "ciao"');

        $processes = null;
        $this->assertTrue($manager->assertNProcessRunning($queue, $processes));
    }

    /**src/UI/VerboseRenderer.php
     * @test
     */
    public function shouldCreateProcessesWithoutBeforeProcessExecutingFactoryWithTheCorrectArguments(): void
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
            ->willReturn(new Process(['echo '], (string) rand()));

        $manager = new ProcessesManager(1, $factory);

        $this->assertTrue($manager->assertNProcessRunning($queue, $processes));
    }

    /**
     * @test
     */
    public function shouldCreate6ProcessesGivingThemTheCorrectEnvParameters(): void
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
            ->method('add');
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
                ->willReturn(new Process(['echo '], (string) rand()));
        }

        $manager = new ProcessesManager(1, $factory);

        $this->assertTrue($manager->assertNProcessRunning($queue, $processes));
        $this->assertTrue($manager->assertNProcessRunning($queue, $processes));
    }
}
