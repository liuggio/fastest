<?php

namespace Liuggio\Fastest\Process;

class ProcessFactoryTest extends \PHPUnit\Framework\TestCase
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
            $_ENV + [
                'ENV_TEST_CHANNEL'              => 2,
                'ENV_TEST_CHANNEL_READABLE'     => 'test_2',
                'ENV_TEST_CHANNELS_NUMBER'      => 10,
                'ENV_TEST_ARGUMENT'             => 'fileA',
                'ENV_TEST_INC_NUMBER'           => 10,
                'ENV_TEST_IS_FIRST_ON_CHANNEL'  => 1,
                'PATH'                          => getenv('PATH')
            ],
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
            $_ENV + [
                'ENV_TEST_CHANNEL'              => 2,
                'ENV_TEST_CHANNEL_READABLE'     => 'test_2',
                'ENV_TEST_CHANNELS_NUMBER'      => 11,
                'ENV_TEST_ARGUMENT'             => 'fileA',
                'ENV_TEST_INC_NUMBER'           => 12,
                'ENV_TEST_IS_FIRST_ON_CHANNEL'  => 0,
                'PATH'                          => getenv('PATH')
            ],
            $process->getenv()
        );
    }

    /**
     * @test
     */
    public function shouldReplaceThePlaceholder()
    {
        $factory = new ProcessFactory(12, 'execute {p} {} {n}');
        $process = $factory->createAProcess('fileA', 1, 13, true);

        $this->assertEquals('execute 1 fileA 13', $process->getCommandLine());
        $this->assertEquals(
            $_ENV + [
                'ENV_TEST_CHANNEL'              => 1,
                'ENV_TEST_CHANNEL_READABLE'     => 'test_1',
                'ENV_TEST_CHANNELS_NUMBER'      => 12,
                'ENV_TEST_ARGUMENT'             => 'fileA',
                'ENV_TEST_INC_NUMBER'           => 13,
                'ENV_TEST_IS_FIRST_ON_CHANNEL'  => 1,
                'PATH'                          => getenv('PATH')
            ],
            $process->getenv()
        );
    }
}
