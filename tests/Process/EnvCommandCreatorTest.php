<?php

declare(strict_types=1);

namespace Liuggio\Fastest\Process;

use Liuggio\Fastest\Trait\ServerDataTrait;
use PHPUnit\Framework\TestCase;

class EnvCommandCreatorTest extends TestCase
{
    use ServerDataTrait;

    /**
     * @test
     */
    public function shouldNotOverrideFastestEnvVariablesWithServerSuperglobal(): void
    {
        $_SERVER[EnvCommandCreator::ENV_TEST_CHANNEL] = 0;
        $_SERVER[EnvCommandCreator::ENV_TEST_CHANNEL_READABLE] = 'test_0';
        $_SERVER[EnvCommandCreator::ENV_TEST_CHANNELS_NUMBER] = 10;
        $_SERVER[EnvCommandCreator::ENV_TEST_ARGUMENT] = 'a_test_command';
        $_SERVER[EnvCommandCreator::ENV_TEST_INCREMENTAL_NUMBER] = 1;
        $_SERVER[EnvCommandCreator::ENV_TEST_IS_FIRST_ON_CHANNEL] = 0;

        $envCommandCreator = new EnvCommandCreator();

        $res = $envCommandCreator->execute(1, 5, 'exec_test_command', 4, true);

        $this->assertArrayHasKey(EnvCommandCreator::ENV_TEST_CHANNEL, $res);
        $this->assertEquals(1, $res[EnvCommandCreator::ENV_TEST_CHANNEL]);

        $this->assertArrayHasKey(EnvCommandCreator::ENV_TEST_CHANNEL_READABLE, $res);
        $this->assertEquals('test_1', $res[EnvCommandCreator::ENV_TEST_CHANNEL_READABLE]);

        $this->assertArrayHasKey(EnvCommandCreator::ENV_TEST_CHANNELS_NUMBER, $res);
        $this->assertEquals(5, $res[EnvCommandCreator::ENV_TEST_CHANNELS_NUMBER]);

        $this->assertArrayHasKey(EnvCommandCreator::ENV_TEST_ARGUMENT, $res);
        $this->assertEquals('exec_test_command', $res[EnvCommandCreator::ENV_TEST_ARGUMENT]);

        $this->assertArrayHasKey(EnvCommandCreator::ENV_TEST_INCREMENTAL_NUMBER, $res);
        $this->assertEquals(4, $res[EnvCommandCreator::ENV_TEST_INCREMENTAL_NUMBER]);

        $this->assertArrayHasKey(EnvCommandCreator::ENV_TEST_IS_FIRST_ON_CHANNEL, $res);
        $this->assertEquals(1, $res[EnvCommandCreator::ENV_TEST_IS_FIRST_ON_CHANNEL]);
    }

    /**
     * @test
     */
    public function shouldNotOverrideFastestEnvVariablesWithEnvSuperglobal(): void
    {
        $_ENV[EnvCommandCreator::ENV_TEST_CHANNEL] = 0;
        $_ENV[EnvCommandCreator::ENV_TEST_CHANNEL_READABLE] = 'test_0';
        $_ENV[EnvCommandCreator::ENV_TEST_CHANNELS_NUMBER] = 10;
        $_ENV[EnvCommandCreator::ENV_TEST_ARGUMENT] = 'a_test_command';
        $_ENV[EnvCommandCreator::ENV_TEST_INCREMENTAL_NUMBER] = 1;
        $_ENV[EnvCommandCreator::ENV_TEST_IS_FIRST_ON_CHANNEL] = 0;

        $envCommandCreator = new EnvCommandCreator();

        $res = $envCommandCreator->execute(1, 5, 'exec_test_command', 4, true);

        $this->assertArrayHasKey(EnvCommandCreator::ENV_TEST_CHANNEL, $res);
        $this->assertEquals(1, $res[EnvCommandCreator::ENV_TEST_CHANNEL]);

        $this->assertArrayHasKey(EnvCommandCreator::ENV_TEST_CHANNEL_READABLE, $res);
        $this->assertEquals('test_1', $res[EnvCommandCreator::ENV_TEST_CHANNEL_READABLE]);

        $this->assertArrayHasKey(EnvCommandCreator::ENV_TEST_CHANNELS_NUMBER, $res);
        $this->assertEquals(5, $res[EnvCommandCreator::ENV_TEST_CHANNELS_NUMBER]);

        $this->assertArrayHasKey(EnvCommandCreator::ENV_TEST_ARGUMENT, $res);
        $this->assertEquals('exec_test_command', $res[EnvCommandCreator::ENV_TEST_ARGUMENT]);

        $this->assertArrayHasKey(EnvCommandCreator::ENV_TEST_INCREMENTAL_NUMBER, $res);
        $this->assertEquals(4, $res[EnvCommandCreator::ENV_TEST_INCREMENTAL_NUMBER]);

        $this->assertArrayHasKey(EnvCommandCreator::ENV_TEST_IS_FIRST_ON_CHANNEL, $res);
        $this->assertEquals(1, $res[EnvCommandCreator::ENV_TEST_IS_FIRST_ON_CHANNEL]);
    }

    /**
     * @test
     */
    public function shouldMergeEnvVariables(): void
    {
        $_SERVER['a_variable'] = 'a_variable_value_server';
        $_ENV['A_VARIABLE'] = 'a_variable_value_env';

        $_SERVER['ANOTHER_VARIABLE'] = 'another_variable_value_server';
        $_ENV['another_variable'] = 'another_variable_value_env';

        $envCommandCreator = new EnvCommandCreator();

        $res = $envCommandCreator->execute(1, 5, 'exec_test_command', 4, true);

        unset($_ENV['A_VARIABLE'], $_ENV['another_variable']);

        $this->assertEquals(
            [
                EnvCommandCreator::ENV_TEST_CHANNEL => 1,
                EnvCommandCreator::ENV_TEST_CHANNEL_READABLE => 'test_1',
                EnvCommandCreator::ENV_TEST_CHANNELS_NUMBER => 5,
                EnvCommandCreator::ENV_TEST_ARGUMENT => 'exec_test_command',
                EnvCommandCreator::ENV_TEST_INCREMENTAL_NUMBER => 4,
                EnvCommandCreator::ENV_TEST_IS_FIRST_ON_CHANNEL => 1,
            ] + $this->getServerWithDecomposeArgv() + $_ENV,
            $res
        );
    }

    /**
     * @test
     */
    public function shouldMergeArrayEnvVariables(): void
    {
        $_SERVER['my_custom_array'] = [
            'sub_array' => [
                'another_array' => 'value_env',
                'another_array_2' => 'value_env_2',
            ],
            'sub_array2' => 'value_env',
        ];

        $envCommandCreator = new EnvCommandCreator();

        $res = $envCommandCreator->execute(1, 5, 'exec_test_command', 4, true);

        $this->assertArrayHasKey('my_custom_array_sub_array_another_array', $res);
        $this->assertEquals('value_env', $res['my_custom_array_sub_array_another_array']);
        $this->assertArrayHasKey('my_custom_array_sub_array_another_array_2', $res);
        $this->assertEquals('value_env_2', $res['my_custom_array_sub_array_another_array_2']);
        $this->assertArrayHasKey('my_custom_array_sub_array2', $res);
        $this->assertEquals('value_env', $res['my_custom_array_sub_array2']);
        $this->assertArrayNotHasKey('my_custom_array', $res);
    }

    /**
     * @test
     */
    public function shouldntMergeArrayEnvVariables(): void
    {
        $_SERVER['my_custom_array'] = 'my_important_value_env';
        $_SERVER['my_custom'] = [
            'array' => 'my_useless_value_env'
        ];

        $envCommandCreator = new EnvCommandCreator();

        $res = $envCommandCreator->execute(1, 5, 'exec_test_command', 4, true);

        $this->assertArrayHasKey('my_custom_array', $res);
        $this->assertEquals('my_important_value_env', $res['my_custom_array']);
        $this->assertArrayNotHasKey('my_custom', $res);
    }
}