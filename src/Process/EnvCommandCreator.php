<?php

namespace Liuggio\Fastest\Process;

class EnvCommandCreator
{
    const ENV_TEST_NUMBER='TEST_ENV_NUMBER';
    const ENV_TEST_DB_NAME='ENV_TEST_DB_NAME';
    const ENV_TEST_MAX_PROCESSES='ENV_TEST_MAX_PROCESSES';
    const ENV_TEST_SUITE_NAME='ENV_TEST_SUITE_NAME';
    const ENV_TEST_INCREMENTAL_NUMBER='ENV_TEST_INC_NUMBER';
    const ENV_TEST_IS_FIRST_ON_ITS_THREAD='ENV_TEST_IS_FIRST';
    // create an array of env
    public function execute($i, $maxProcesses, $suite, $currentProcessCounter, $isFirstOnItsThread = false)
    {
        return array(
            self::ENV_TEST_NUMBER.'='.(int) $i,
            self::ENV_TEST_DB_NAME.'=test_'.(int) $i,
            self::ENV_TEST_MAX_PROCESSES.'='.(int) $maxProcesses,
            self::ENV_TEST_SUITE_NAME.'='.$suite,
            self::ENV_TEST_INCREMENTAL_NUMBER.'='.(int) $currentProcessCounter,
            self::ENV_TEST_IS_FIRST_ON_ITS_THREAD.'='.(int) $isFirstOnItsThread
        );
    }
}
