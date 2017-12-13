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

    // create an array of env
    public function execute($i, $maxProcesses, $suite, $currentProcessCounter, $isFirstOnItsThread = false)
    {
        return array_change_key_case(static::cleanEnvVariables($_SERVER) + $_ENV + [
            self::ENV_TEST_CHANNEL => (int) $i,
            self::ENV_TEST_CHANNEL_READABLE => 'test_'.$i,
            self::ENV_TEST_CHANNELS_NUMBER => (int) $maxProcesses,
            self::ENV_TEST_ARGUMENT => (string) $suite,
            self::ENV_TEST_INCREMENTAL_NUMBER => (int) $currentProcessCounter,
            self::ENV_TEST_IS_FIRST_ON_CHANNEL => (int) $isFirstOnItsThread,
        ], CASE_UPPER);
    }

    /**
     * @param array $variables
     *
     * @return array
     */
    public static function cleanEnvVariables(array $variables)
    {
        return array_filter(
            $variables,
            function ($key) {
                return strpos($key, 'ENV_TEST_') !== 0;
            },
            ARRAY_FILTER_USE_KEY
        );
    }
}
