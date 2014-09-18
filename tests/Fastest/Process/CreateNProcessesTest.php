<?php

namespace Liuggio\Fastest\Process;


class CreateNProcessesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldExecuteNumberOfCPUProcesses()
    {
        $processCount = $this->getMock('Liuggio\Fastest\Process\ProcessorCounter');
        $processCount->expects($this->once())
            ->method('execute')
            ->will($this->returnValue(32));

        $nProcesses = new CreateNProcesses($processCount);
        $nProcesses = $nProcesses->execute('ls -d .');

        $this->assertEquals(32, $nProcesses->count());
        $this->assertEquals('ls -d .', $nProcesses->get(1)->getCommandLine());
        $this->assertCount(3, $nProcesses->get(1)->getenv());
    }

    /**
     * @test
     */
    public function shouldExecuteANumberOfProcessesLimitedByInput()
    {
        $nProcesses = new CreateNProcesses();

        $this->assertEquals(1, $nProcesses->execute('ls -d .', 1)->count());
    }
}
 