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
        $env = [
            self::ENV_TEST_CHANNEL => $i,
            self::ENV_TEST_CHANNEL_READABLE => 'test_'.$i,
            self::ENV_TEST_CHANNELS_NUMBER => $maxProcesses,
            self::ENV_TEST_ARGUMENT => $test,
            self::ENV_TEST_INCREMENTAL_NUMBER => $currentProcessCounter,
            self::ENV_TEST_IS_FIRST_ON_CHANNEL => (int) $isFirstOnItsThread, // @todo should this be bool?
        ] + $_SERVER + $_ENV;

        $res = [];
        $mergedArgs = [];
        foreach ($env as $key => $value) {
            if (array_key_exists(strtolower($key), $mergedArgs)) {
                continue;
            }

            $res[$key] = $value;
            $mergedArgs[strtolower($key)] = true;
        }

        return $res;
    }
}
