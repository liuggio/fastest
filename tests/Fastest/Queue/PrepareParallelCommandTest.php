<?php

namespace Liuggio\Fastest\Queue;


class PrepareParallelCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldCreateACommandUsingParallelTests()
    {
        $commandUseCase = new PrepareParallelCommand('sub');
        $command = $commandUseCase->execute();
        $this->assertEquals('parallel_test  -e "php sub consume  -l"', $command);
    }

    /**
     * @test
     */
    public function shouldCreateACommandUsingParallelTestsWithOptions()
    {
        $commandUseCase = new PrepareParallelCommand('sub');
        $command = $commandUseCase->execute('sub_sub', 2, '/tmp');
        $this->assertEquals('parallel_test -n 2 -e "php sub consume \'sub_sub\' -l"', $command);
    }
}
 