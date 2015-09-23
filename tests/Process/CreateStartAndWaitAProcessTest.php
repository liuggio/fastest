<?php

namespace Liuggio\Fastest\Process;

use Liuggio\Fastest\Channel;
use Liuggio\Fastest\CommandLine;
use Liuggio\Fastest\InputLine;

class CreateStartAndWaitAProcessTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldDispatchALotOfEvents()
    {
        $createStartAndWait = new CreateAndStartAProcess();
        $process = $createStartAndWait->createAndStartAProcess(
            Channel::createAWaiting(1, 2),
            new CommandLine('sleep 2;echo -n "AAA";echo {p} {}'),
            InputLine::fromString('tests/'),
            120
        );

        $this->assertInstanceOf('Liuggio\Fastest\Process\Process', $process);
        $this->assertEquals('sleep 2;echo -n "AAA";echo 1 tests/', $process->getCommandLine());
        $this->assertEquals('1', $process->getChannel());
    }
}
