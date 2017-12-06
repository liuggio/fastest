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
        return array_merge(
            $_ENV,
            [
                self::ENV_TEST_CHANNEL.'='.(int) $i,
                self::ENV_TEST_CHANNEL_READABLE.'=test_'.(int) $i,
                self::ENV_TEST_CHANNELS_NUMBER.'='.(int) $maxProcesses,
                self::ENV_TEST_ARGUMENT.'='.$suite,
                self::ENV_TEST_INCREMENTAL_NUMBER.'='.(int) $currentProcessCounter,
                self::ENV_TEST_IS_FIRST_ON_CHANNEL.'='.(int) $isFirstOnItsThread,
                'PATH='.getenv('PATH'),
            ]
        );
    }
}
