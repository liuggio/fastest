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

        $processEnv = $process->getEnv();
        $envTestVars = $this->filterEnvTestVariables($processEnv);

        $this->assertEquals($envTestVars, [
            'ENV_TEST_CHANNEL' => 2,
            'ENV_TEST_CHANNEL_READABLE' => 'test_2',
            'ENV_TEST_CHANNELS_NUMBER' => 10,
            'ENV_TEST_ARGUMENT'=> 'fileA',
            'ENV_TEST_INC_NUMBER' => 10,
            'ENV_TEST_IS_FIRST_ON_CHANNEL' => 1,
        ]);
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

        $processEnv = $process->getEnv();
        $envTestVars = $this->filterEnvTestVariables($processEnv);

        $this->assertEquals($envTestVars, [
            'ENV_TEST_CHANNEL' => 2,
            'ENV_TEST_CHANNEL_READABLE' => 'test_2',
            'ENV_TEST_CHANNELS_NUMBER' => 11,
            'ENV_TEST_ARGUMENT'=> 'fileA',
            'ENV_TEST_INC_NUMBER' => 12,
            'ENV_TEST_IS_FIRST_ON_CHANNEL' => 0,
        ]);
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

        $processEnv = $process->getEnv();
        $envTestVars = $this->filterEnvTestVariables($processEnv);

        $this->assertEquals($envTestVars, [
            'ENV_TEST_CHANNEL' => 1,
            'ENV_TEST_CHANNEL_READABLE' => 'test_1',
            'ENV_TEST_CHANNELS_NUMBER' => 12,
            'ENV_TEST_ARGUMENT'=> 'fileA',
            'ENV_TEST_INC_NUMBER' => 13,
            'ENV_TEST_IS_FIRST_ON_CHANNEL' => 1,
        ]);
    }

    /**
     * @test
     */
    public function shouldIncludeServerVarsInCreatedCommand()
    {
        $factory = new ProcessFactory(10);
        $process = $factory->createAProcess('fileA', 2, 10, true);
        $serverEnvs = $_SERVER;

        unset($serverEnvs['argv']);

        $processEnv = $process->getEnv();

        $this->assertEmpty(array_diff_key(array_change_key_case($serverEnvs), $processEnv));
    }

    /**
     * @test
     */
    public function shouldIncludeEnvVarsInCreatedCommand()
    {
        $factory = new ProcessFactory(10);
        $process = $factory->createAProcess('fileA', 2, 10, true);
        $serverEnvs = $_SERVER;

        unset($serverEnvs['argv']);

        $processEnv = $process->getEnv();

        $this->assertEmpty(array_diff_key(array_change_key_case($_ENV), $processEnv));
    }

    /**
     * @param array $processEnv
     *
     * @return array
     */
    private function filterEnvTestVariables(array $processEnv)
    {
        return array_filter(
            $processEnv,
            function ($key) {
                return strpos($key, 'ENV_TEST_') !== false;
            },
            ARRAY_FILTER_USE_KEY
        );
    }
}
