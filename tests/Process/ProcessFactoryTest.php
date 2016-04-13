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

        $this->assertEquals('bin/phpunit fileA', $process->getCommandLine());
        $this->assertEquals(
            $_ENV + array(
                0 => 'ENV_TEST_CHANNEL=2',
                1 => 'ENV_TEST_CHANNEL_READABLE=test_2',
                2 => 'ENV_TEST_CHANNELS_NUMBER=10',
                3 => 'ENV_TEST_ARGUMENT=fileA',
                4 => 'ENV_TEST_INC_NUMBER=10',
                5 => 'ENV_TEST_IS_FIRST_ON_CHANNEL=1',
                6 => 'PATH='.getenv('PATH')
            ),
            $process->getenv()
        );
    }

    /**
     * @test
     */
    public function shouldCreateACommandUsingParallelTestsWithOptions()
    {
        $factory = new ProcessFactory(11, 'execute');
        $process = $factory->createAProcess('fileA', 2, 12, false);

        $this->assertEquals('execute', $process->getCommandLine());
        $this->assertEquals(
            $_ENV + array(
                0 => 'ENV_TEST_CHANNEL=2',
                1 => 'ENV_TEST_CHANNEL_READABLE=test_2',
                2 => 'ENV_TEST_CHANNELS_NUMBER=11',
                3 => 'ENV_TEST_ARGUMENT=fileA',
                4 => 'ENV_TEST_INC_NUMBER=12',
                5 => 'ENV_TEST_IS_FIRST_ON_CHANNEL=0',
                6 => 'PATH='.getenv('PATH')
            ),
            $process->getenv()
        );
    }

    /**
     * @test
     */
    public function shouldReplaceThePlaceholder()
    {
        $factory = new ProcessFactory(12, 'execute {p} {}');
        $process = $factory->createAProcess('fileA', 1, 13, true);

        $this->assertEquals('execute 1 fileA', $process->getCommandLine());
        $this->assertEquals(
            $_ENV + array(
                0 => 'ENV_TEST_CHANNEL=1',
                1 => 'ENV_TEST_CHANNEL_READABLE=test_1',
                2 => 'ENV_TEST_CHANNELS_NUMBER=12',
                3 => 'ENV_TEST_ARGUMENT=fileA',
                4 => 'ENV_TEST_INC_NUMBER=13',
                5 => 'ENV_TEST_IS_FIRST_ON_CHANNEL=1',
                6 => 'PATH='.getenv('PATH')
            ),
            $process->getenv()
        );
    }
}
 
