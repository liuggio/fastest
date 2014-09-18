<?php

namespace Liuggio\Fastest\Process;


class PrepareCommandToExecuteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldCreateACommandUsingParallelTests()
    {
        $commandUseCase = new PrepareCommandToExecute();
        $command = $commandUseCase->execute('filepath');
        $this->assertEquals('php fastest co:si "filepath" "bin/phpunit {}"', $command);
    }

    /**
     * @test
     */
    public function shouldCreateACommandUsingParallelTestsWithOptions()
    {
        $commandUseCase = new PrepareCommandToExecute('para');
        $command = $commandUseCase->execute('filepath', 'execute');
        $this->assertEquals('php para co:si "filepath" "execute"', $command);
    }
}
 