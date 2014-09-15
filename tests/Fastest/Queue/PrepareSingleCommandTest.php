<?php

namespace Liuggio\Fastest\Queue;

define(SCRIPT_NAME,'single');

class PrepareSingleCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldCreateACommand()
    {
        $commandUseCase = new PrepareSingleCommand();
        $command = $commandUseCase->execute('sub_sub');
        $this->assertEquals('php single consume "sub_sub" -l', $command);
    }

    /**
     * @test
     */
    public function shouldCreateACommandWithOptions()
    {
        $commandUseCase = new PrepareSingleCommand('single');
        $command = $commandUseCase->execute('sub_sub');
        $this->assertEquals('php single consume "sub_sub" -l', $command);
    }
}
 