<?php

namespace Liuggio\Fastest\Queue;

use Liuggio\Fastest\AppParametersFromEnv;

class AppParametersFromEnvTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldFillTheParametersGettingValueFromENV()
    {
        $logPath = $this->setEnv('LOG_PATH');
        $logLevel = $this->setEnv('LOG_LEVEL');
        $redisHost = $this->setEnv('REDIS_HOSTNAME');
        $redisPort = $this->setEnv('REDIS_PORT');
        $redisQueueName = $this->setEnv('REDIS_QUEUE');

        $parameters = new AppParametersFromEnv();

        $this->assertEquals($logPath, $parameters->getLogPath());
        $this->assertEquals($logLevel, $parameters->getLogLevel());
        $this->assertEquals($redisHost, $parameters->getRedisHost());
        $this->assertEquals($redisPort, $parameters->getRedisPort());
        $this->assertEquals($redisQueueName, $parameters->getRedisQueueName());
    }

    private function setEnv($env)
    {
        putenv($env."=".$env);

        return $env;
    }

}
 