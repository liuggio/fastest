<?php

namespace Liuggio\Fastest\Process;

use Symfony\Component\Process\Process;

class ProcessesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function shouldStartAllTheItems()
    {
        $process = $this->mockProcessWithExpectation('start');

        $processes = new Processes([$process]);

        $this->assertTrue($processes->start());
    }

    /**
     * @test
     */
    public function shouldStopAllTheItems()
    {
        $process = $this->mockProcessWithExpectation('stop');

        $processes = new Processes([$process]);

        $this->assertTrue($processes->stop());
    }

    /**
     * @test
     */
    public function shouldWaitAllTheItems()
    {
        $process = $this->getMockBuilder(Process::class)
            ->disableOriginalConstructor()
            ->getMock();
        $process
            ->expects($this->exactly(5))
            ->method('isTerminated')
            ->willReturn(false)
            ->willReturnOnConsecutiveCalls(false, false, false, true)
        ;

        $processes = new Processes([$process]);

        $this->assertTrue($processes->wait());
    }

    /**
     * @test
     */
    public function shouldNotAddTerminatedProcessToReportBufferOnCleanUp()
    {
        $process = $this->createMock(Process::class);
        $process
            ->method('isTerminated')
            ->willReturn(true);
        $process
            ->method('isSuccessful')
            ->willReturn(true);

        $processes = new Processes([$process]);
        $processes->start(0);

        $processes->cleanUP(false);

        $this->assertAttributeEmpty('totalBuffer', $processes);
    }

    /**
     * @test
     */
    public function shouldAddTerminatedProcessToReportBufferOnCleanUp()
    {
        $process = $this->createMock(Process::class);
        $process
            ->method('isTerminated')
            ->willReturn(true);
        $process
            ->method('isSuccessful')
            ->willReturn(true);

        $processes = new Processes([$process]);
        $processes->start(0);

        $processes->cleanUP();

        $this->assertAttributeCount(1, 'totalBuffer', $processes);
    }

    /**
     * @test
     */
    public function shouldNotAddTerminatedProcessToReportBufferAfterWaitFinish()
    {
        $process = $this->createMock(Process::class);
        $process
            ->method('isTerminated')
            ->willReturnOnConsecutiveCalls(false, true);
        $process
            ->method('isSuccessful')
            ->willReturn(true);

        $process2 = $this->createMock(Process::class);
        $process2
            ->method('isTerminated')
            ->willReturn(true);
        $process2
            ->method('isSuccessful')
            ->willReturnOnConsecutiveCalls(false, true);

        $processes = new Processes([$process, $process2]);
        $processes->start(0);
        $processes->start(1);

        $processes->wait(null, false);

        $this->assertAttributeEmpty('totalBuffer', $processes);
    }

    /**
     * @test
     */
    public function shouldAddTerminatedProcessToReportBufferAfterWaitFinish()
    {
        $process = $this->createMock(Process::class);
        $process
            ->method('isTerminated')
            ->willReturnOnConsecutiveCalls(false, true);
        $process
            ->method('isSuccessful')
            ->willReturn(true);

        $process2 = $this->createMock(Process::class);
        $process2
            ->method('isTerminated')
            ->willReturn(true);
        $process2
            ->method('isSuccessful')
            ->willReturnOnConsecutiveCalls(false, true);

        $processes = new Processes([$process, $process2]);
        $processes->start(0);
        $processes->start(1);

        $processes->wait();

        $this->assertAttributeCount(1, 'totalBuffer', $processes);
    }

    protected function mockProcessWithExpectation($method)
    {
        $process = $this->getMockBuilder(Process::class)
            ->disableOriginalConstructor()
            ->getMock();
        $process
            ->expects($this->once())
            ->method($method);

        return $process;
    }
}
