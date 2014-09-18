<?php

namespace Liuggio\Fastest\Process;


use Liuggio\Fastest\Process\ProcessorCounter;

class ProcessesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldStartAllTheItems()
    {
        $process = $this->mockProcessWithExpectation('start');

        $processes = new Processes(array($process));

        $this->assertTrue($processes->start());
    }

    /**
     * @test
     */
    public function shouldStopAllTheItems()
    {
        $process = $this->mockProcessWithExpectation('stop');

        $processes = new Processes(array($process));

        $this->assertTrue($processes->stop());
    }
    /**
     * @test
     */
    public function shouldWaitAllTheItems()
    {
        $process = $this->mockProcessWithExpectation('wait');

        $processes = new Processes(array($process));

        $this->assertTrue($processes->wait());
    }

    protected function mockProcessWithExpectation($method)
    {
        $process = $this->getMockBuilder('\Symfony\Component\Process\Process')
            ->disableOriginalConstructor()
            ->getMock();
        $process
            ->expects($this->once())
            ->method($method);

        return $process;
    }
}
 