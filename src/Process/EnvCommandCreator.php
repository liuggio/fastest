<?php

namespace Liuggio\Fastest\Process;

class EnvCommandCreator
{
    const ENV_TEST_CHANNEL = 'ENV_TEST_CHANNEL';
    const ENV_TEST_CHANNEL_READABLE = 'ENV_TEST_CHANNEL_READABLE';
    const ENV_TEST_CHANNELS_NUMBER = 'ENV_TEST_CHANNELS_NUMBER';
    const ENV_TEST_ARGUMENT = 'ENV_TEST_ARGUMENT';
    const ENV_TEST_INCREMENTAL_NUMBER = 'ENV_TEST_INC_NUMBER';
    const ENV_TEST_IS_FIRST_ON_CHANNEL = 'ENV_TEST_IS_FIRST_ON_CHANNEL';

    /**
     * @param int $i
     * @param int $maxProcesses
     * @param string $test
     * @param int $currentProcessCounter
     * @param bool $isFirstOnItsThread
     *
     * @return array<string, mixed>
     */
    public function execute(
        int $i,
        int $maxProcesses,
        string $test,
        int $currentProcessCounter,
        bool $isFirstOnItsThread = false
    ): array {
        return array_change_key_case(static::cleanEnvVariables($_SERVER) + $_ENV + [
            self::ENV_TEST_CHANNEL => $i,
            self::ENV_TEST_CHANNEL_READABLE => 'test_'.$i,
            self::ENV_TEST_CHANNELS_NUMBER => $maxProcesses,
            self::ENV_TEST_ARGUMENT => $test,
            self::ENV_TEST_INCREMENTAL_NUMBER => $currentProcessCounter,
            self::ENV_TEST_IS_FIRST_ON_CHANNEL => (int) $isFirstOnItsThread, // @todo should this be bool?
        ], CASE_UPPER);
    }

    /**
     * @param array<string, mixed> $variables
     *
     * @return array<string, mixed>
     */
    public static function cleanEnvVariables(array $variables): array
    {
        return array_filter(
            $variables,
            function ($key) {
                return 0 !== strpos($key, 'ENV_TEST_');
            },
            ARRAY_FILTER_USE_KEY
        );
    }
}
