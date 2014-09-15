<?php

namespace Liuggio\Fastest\Queue;


class PrepareParallelCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldCreateACommandUsingParallelTests()
    {
        $commandUseCase = new PrepareParallelCommand();
        $command = $commandUseCase->execute('sub');
        $this->assertEquals('parallel_test  -e "sub"', $command);
    }

    /**
     * @test
     */
    public function shouldCreateACommandUsingParallelTestsWithOptions()
    {
        $commandUseCase = new PrepareParallelCommand('para');
        $command = $commandUseCase->execute('sub_sub', 2);
        $this->assertEquals('para -n 2 -e "sub_sub"', $command);
    }
}
 