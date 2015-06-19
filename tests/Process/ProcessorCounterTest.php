<?php

namespace Liuggio\Fastest\Process;


use Liuggio\Fastest\Process\ProcessorCounter;

class ProcessorCounterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldCountTheNumberOfProcessorInLinux()
    {
        $processorCount = new ProcessorCounter();

        $this->assertEquals(4, $processorCount->execute());
    }
}
 
