<?php

namespace Liuggio\Fastest;


class ExecuteACommandInParallelTest extends \PHPUnit_Framework_TestCase {

    /**
     * @test
     */
    public function shouldStartAndWaitOnProcesses()
    {
        $mockObject = $this->getMockBuilder('\Liuggio\Fastest\Process\Processes')
            ->disableOriginalConstructor()
            ->getMock();
        $mockObject->expects($this->once())
            ->method('start');
        $mockObject->expects($this->any())
            ->method('wait');

        $factory = $this->getMock('\Liuggio\Fastest\Process\CreateNProcesses');
        $factory->expects($this->once())
            ->method('execute')
            ->willReturn($mockObject);

        $executor = new ExecuteACommandInParallel($factory);
        $executor->execute('ls');
    }
}
