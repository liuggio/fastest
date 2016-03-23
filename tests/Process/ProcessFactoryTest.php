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
        $process = $factory->createAProcess('fileA', 2, 10, true);

        $expectedVariables = array(
            'ENV_TEST_CHANNEL'             => 2,
            'ENV_TEST_CHANNEL_READABLE'    => 'test_2',
            'ENV_TEST_CHANNELS_NUMBER'     => 10,
            'ENV_TEST_ARGUMENT'            => 'fileA',
            'ENV_TEST_INC_NUMBER'          => 10,
            'ENV_TEST_IS_FIRST_ON_CHANNEL' => 1,
        );

        $this->assertEquals('bin/phpunit fileA', $process->getCommandLine());

        foreach($expectedVariables as $key => $value) {
            $this->assertEquals($value, getenv($key));
        }
    }

    /**
     * @test
     */
    public function shouldCreateACommandUsingParallelTestsWithOptions()
    {
        $factory = new ProcessFactory(11, 'execute');
        $process = $factory->createAProcess('fileA', 2, 12, false);

        $expectedVariables = array(
            'ENV_TEST_CHANNEL'             => 2,
            'ENV_TEST_CHANNEL_READABLE'    => 'test_2',
            'ENV_TEST_CHANNELS_NUMBER'     => 11,
            'ENV_TEST_ARGUMENT'            => 'fileA',
            'ENV_TEST_INC_NUMBER'          => 12,
            'ENV_TEST_IS_FIRST_ON_CHANNEL' => 0,
        );

        $this->assertEquals('execute', $process->getCommandLine());

        foreach($expectedVariables as $key => $value) {
            $this->assertEquals($value, getenv($key));
        }
    }

    /**
     * @test
     */
    public function shouldReplaceThePlaceholder()
    {
        $factory = new ProcessFactory(12, 'execute {p} {}');
        $process = $factory->createAProcess('fileA', 1, 13, true);

        $expectedVariables = array(
            'ENV_TEST_CHANNEL'             => 1,
            'ENV_TEST_CHANNEL_READABLE'    => 'test_1',
            'ENV_TEST_CHANNELS_NUMBER'     => 12,
            'ENV_TEST_ARGUMENT'            => 'fileA',
            'ENV_TEST_INC_NUMBER'          => 13,
            'ENV_TEST_IS_FIRST_ON_CHANNEL' => 1,
        );

        $this->assertEquals('execute 1 fileA', $process->getCommandLine());

        foreach($expectedVariables as $key => $value) {
            $this->assertEquals($value, getenv($key));
        }
    }
}
 
