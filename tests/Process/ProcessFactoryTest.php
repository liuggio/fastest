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
        $serverEnvs = $_SERVER;
        unset($serverEnvs['argv']);

        $this->assertEquals('bin'.DIRECTORY_SEPARATOR.'phpunit fileA', $process->getCommandLine());
        $this->assertEquals(
            array_change_key_case($serverEnvs + $_ENV + [
                'ENV_TEST_CHANNEL' => 2,
                'ENV_TEST_CHANNEL_READABLE' => 'test_2',
                'ENV_TEST_CHANNELS_NUMBER' => 10,
                'ENV_TEST_ARGUMENT'=> 'fileA',
                'ENV_TEST_INC_NUMBER' => 10,
                'ENV_TEST_IS_FIRST_ON_CHANNEL' => 1,
            ], CASE_UPPER),
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
        $serverEnvs = $_SERVER;
        unset($serverEnvs['argv']);

        $this->assertEquals('execute', $process->getCommandLine());
        $this->assertEquals(
            array_change_key_case($serverEnvs + $_ENV + [
                'ENV_TEST_CHANNEL' => 2,
                'ENV_TEST_CHANNEL_READABLE' => 'test_2',
                'ENV_TEST_CHANNELS_NUMBER' => 11,
                'ENV_TEST_ARGUMENT'=> 'fileA',
                'ENV_TEST_INC_NUMBER' => 12,
                'ENV_TEST_IS_FIRST_ON_CHANNEL' => 0,
            ], CASE_UPPER),
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
        $serverEnvs = $_SERVER;
        unset($serverEnvs['argv']);

        $this->assertEquals('execute 1 fileA 13', $process->getCommandLine());
        $this->assertEquals(
            array_change_key_case($serverEnvs + $_ENV + [
                'ENV_TEST_CHANNEL' => 1,
                'ENV_TEST_CHANNEL_READABLE' => 'test_1',
                'ENV_TEST_CHANNELS_NUMBER' => 12,
                'ENV_TEST_ARGUMENT'=> 'fileA',
                'ENV_TEST_INC_NUMBER' => 13,
                'ENV_TEST_IS_FIRST_ON_CHANNEL' => 1,
            ], CASE_UPPER),
            $process->getenv()
        );
    }
}
