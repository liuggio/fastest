<?php

namespace Liuggio\Fastest\Process;

class ProcessorCounterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function shouldCountTheNumberOfProcessorInLinux()
    {
        $processorCountMock = $this->getMockBuilder(ProcessorCounter::class)
                                   ->setMethods(['getOS'])
                                   ->setConstructorArgs([__DIR__.'/Fixture/proc_cpuinfo'])
                                   ->getMock();

        $processorCountMock->expects($this->any())
                           ->method('getOS')
                           ->will($this->returnValue('Linux'));

        $this->assertEquals(4, $processorCountMock->execute());
    }
}
