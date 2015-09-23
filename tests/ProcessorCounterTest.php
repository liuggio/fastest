<?php

namespace Liuggio\Fastest;

class ProcessorCounterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldCountTheNumberOfProcessorInLinux()
    {
        $processorCount = new ProcessorCounter(__DIR__.'/Fixture/proc_cpuinfo', 'Linux');
        $this->assertEquals(4, $processorCount->execute());
    }
}
