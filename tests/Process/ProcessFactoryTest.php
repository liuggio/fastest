<?php

namespace Liuggio\Fastest\Process;

use PHPUnit\Framework\TestCase;

class ProcessFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function shouldCreateACommandUsingParallelTests(): void
    {
        $factory = new ProcessFactory(10);
        $process = $factory->createAProcess('fileA', 2, 10, true);

        $this->assertEquals('bin'.DIRECTORY_SEPARATOR.'phpunit fileA', $process->getCommandLine());
        $this->assertEquals(
            [
                'ENV_TEST_CHANNEL' => 2,
                'ENV_TEST_CHANNEL_READABLE' => 'test_2',
                'ENV_TEST_CHANNELS_NUMBER' => 10,
                'ENV_TEST_ARGUMENT' => 'fileA',
                'ENV_TEST_INC_NUMBER' => 10,
                'ENV_TEST_IS_FIRST_ON_CHANNEL' => 1,
            ] + $_SERVER + $_ENV,
            $process->getenv()
        );
    }

    /**
     * @test
     */
    public function shouldCreateACommandUsingParallelTestsWithFilteredVariables(): void
    {
        $factory = new ProcessFactory(10);
        $process = $factory->createAProcess('fileA', 2, 10, true);

        $this->assertEquals('bin'.DIRECTORY_SEPARATOR.'phpunit fileA', $process->getCommandLine());

        $processEnv = $process->getEnv();
        $envTestVars = $this->filterEnvTestVariables($processEnv);

        $this->assertEquals([
            'ENV_TEST_CHANNEL' => 2,
            'ENV_TEST_CHANNEL_READABLE' => 'test_2',
            'ENV_TEST_CHANNELS_NUMBER' => 10,
            'ENV_TEST_ARGUMENT' => 'fileA',
            'ENV_TEST_INC_NUMBER' => 10,
            'ENV_TEST_IS_FIRST_ON_CHANNEL' => 1,
        ], $envTestVars);
    }

    /**
     * @param array<string, mixed> $processEnv
     *
     * @return array<string, mixed>
     */
    private function filterEnvTestVariables(array $processEnv)
    {
        return array_filter(
            $processEnv,
            function ($key) {
                return false !== strpos($key, 'ENV_TEST_');
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * @test
     */
    public function shouldCreateACommandUsingParallelTestsWithOptions(): void
    {
        $factory = new ProcessFactory(11, 'execute');
        $process = $factory->createAProcess('fileA', 2, 12);

        $this->assertEquals('execute', $process->getCommandLine());
        $this->assertEquals(
            [
                'ENV_TEST_CHANNEL' => 2,
                'ENV_TEST_CHANNEL_READABLE' => 'test_2',
                'ENV_TEST_CHANNELS_NUMBER' => 11,
                'ENV_TEST_ARGUMENT' => 'fileA',
                'ENV_TEST_INC_NUMBER' => 12,
                'ENV_TEST_IS_FIRST_ON_CHANNEL' => 0,
            ] + $_SERVER + $_ENV,
            $process->getenv()
        );
    }

    /**
     * @test
     */
    public function shouldReplaceThePlaceholder(): void
    {
        $factory = new ProcessFactory(12, 'execute {p} {} {n}');
        $process = $factory->createAProcess('fileA', 1, 13, true);

        $this->assertEquals('execute 1 fileA 13', $process->getCommandLine());
        $this->assertEquals(
            [
                'ENV_TEST_CHANNEL' => 1,
                'ENV_TEST_CHANNEL_READABLE' => 'test_1',
                'ENV_TEST_CHANNELS_NUMBER' => 12,
                'ENV_TEST_ARGUMENT' => 'fileA',
                'ENV_TEST_INC_NUMBER' => 13,
                'ENV_TEST_IS_FIRST_ON_CHANNEL' => 1,
            ] + $_SERVER + $_ENV,
            $process->getenv()
        );
    }
}
