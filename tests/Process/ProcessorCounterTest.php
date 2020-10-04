<?php

namespace Liuggio\Fastest\Process;

use PHPUnit\Framework\TestCase;

class ProcessorCounterTest extends TestCase
{
    /**
     * @test
     */
    public function shouldCountTheNumberOfProcessorInLinux(): void
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
