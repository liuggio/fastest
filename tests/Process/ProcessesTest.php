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
    public function shouldNotAddTerminatedProcessToReportOnCleanUp()
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

        $this->assertEmpty($processes->getReport());
    }

    /**
     * @test
     */
    public function shouldAddTerminatedProcessToReportOnCleanUp()
    {
        $process = $this->createMock(Process::class);
        $process
            ->method('isTerminated')
            ->willReturn(true);
        $process
            ->method('isSuccessful')
            ->willReturn(true);
        $process
            ->method('getEnv')
            ->willReturn([
                EnvCommandCreator::ENV_TEST_ARGUMENT => 'testArg',
                EnvCommandCreator::ENV_TEST_CHANNEL => 1,
                EnvCommandCreator::ENV_TEST_IS_FIRST_ON_CHANNEL => true
            ]);

        $processes = new Processes([$process]);
        $processes->start(0);

        $processes->cleanUP();

        $this->assertCount(1, $processes->getReport());
    }

    /**
     * @test
     */
    public function shouldNotAddTerminatedProcessToReportAfterWaitFinish()
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

        $this->assertEmpty($processes->getReport());
    }

    /**
     * @test
     */
    public function shouldAddTerminatedProcessToReportAfterWaitFinish()
    {
        $process = $this->createMock(Process::class);
        $process
            ->method('isTerminated')
            ->willReturnOnConsecutiveCalls(false, true);
        $process
            ->method('isSuccessful')
            ->willReturn(true);

        $process
            ->method('getEnv')
            ->willReturn([
                EnvCommandCreator::ENV_TEST_ARGUMENT => 'testArg',
                EnvCommandCreator::ENV_TEST_CHANNEL => 1,
                EnvCommandCreator::ENV_TEST_IS_FIRST_ON_CHANNEL => true
            ]);

        $process2 = $this->createMock(Process::class);
        $process2
            ->method('isTerminated')
            ->willReturn(true);
        $process2
            ->method('isSuccessful')
            ->willReturnOnConsecutiveCalls(false, true);

        $process2
            ->method('getEnv')
            ->willReturn([
                EnvCommandCreator::ENV_TEST_ARGUMENT => 'testArgProcess2',
                EnvCommandCreator::ENV_TEST_CHANNEL => 2,
                EnvCommandCreator::ENV_TEST_IS_FIRST_ON_CHANNEL => false
            ]);

        $processes = new Processes([$process, $process2]);
        $processes->start(0);
        $processes->start(1);

        $processes->wait();

        $this->assertCount(1, $processes->getReport());
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
