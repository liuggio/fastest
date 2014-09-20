<?php

namespace Liuggio\Fastest\Process;


class ProcessFactoryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function shouldCreateACommandUsingParallelTests()
    {
        $factory = new ProcessFactory(10);
        $process = $factory->createAProcess('fileA', 2);

        $this->assertEquals('bin/phpunit fileA', $process->getCommandLine());
        $this->assertEquals(array(
            0 => 'TEST_ENV_NUMBER=2',
            1 => 'ENV_TEST_DB_NAME=test_2',
            2 => 'ENV_TEST_MAX_PROCESSES=10',
            3 => 'ENV_TEST_SUITE_NAME=fileA'),
            $process->getenv());
    }

    /**
     * @test
     */
    public function shouldCreateACommandUsingParallelTestsWithOptions()
    {
        $factory = new ProcessFactory(11, 'execute');
        $process = $factory->createAProcess('fileA', 2);

        $this->assertEquals('execute', $process->getCommandLine());
        $this->assertEquals(array(
                0 => 'TEST_ENV_NUMBER=2',
                1 => 'ENV_TEST_DB_NAME=test_2',
                2 => 'ENV_TEST_MAX_PROCESSES=11',
                3 => 'ENV_TEST_SUITE_NAME=fileA'),
            $process->getenv());
    }

    /**
     * @test
     */
    public function shouldReplaceThePlaceholder()
    {
        $factory = new ProcessFactory(12, 'execute {p} {}');
        $process = $factory->createAProcess('fileA', 1);

        $this->assertEquals('execute 1 fileA', $process->getCommandLine());
        $this->assertEquals(array(
                0 => 'TEST_ENV_NUMBER=1',
                1 => 'ENV_TEST_DB_NAME=test_1',
                2 => 'ENV_TEST_MAX_PROCESSES=12',
                3 => 'ENV_TEST_SUITE_NAME=fileA'),
            $process->getenv());
    }
}
 