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
     * Injects parameters into enviroment variables.
     *
     * @param int    $i                     Channel number
     * @param int    $maxProcesses          Max processes
     * @param string $suite                 Suite
     * @param int    $currentProcessCounter Current process counter
     * @param bool   $isFirstOnItsThread    Is first on its thread?
     */
    public function execute($i, $maxProcesses, $suite, $currentProcessCounter, $isFirstOnItsThread = false)
    {
        putenv(self::ENV_TEST_CHANNEL.'='.(int) $i);
        putenv(self::ENV_TEST_CHANNEL_READABLE.'=test_'.(int) $i);
        putenv(self::ENV_TEST_CHANNELS_NUMBER.'='.(int) $maxProcesses);
        putenv(self::ENV_TEST_ARGUMENT.'='.$suite);
        putenv(self::ENV_TEST_INCREMENTAL_NUMBER.'='.(int) $currentProcessCounter);
        putenv(self::ENV_TEST_IS_FIRST_ON_CHANNEL.'='.(int) $isFirstOnItsThread);
    }
}
