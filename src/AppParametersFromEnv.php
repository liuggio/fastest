<?php

namespace Liuggio\Fastest;

use Monolog\Logger;

class AppParametersFromEnv implements AppParametersInterface
{
    CONST DEFAULT_FILE_LOG = 'fastest.log';
    private $logPath;
    private $logLevel;
    private $redisHost;
    private $redisPort;
    private $redisQueueName;

    function __construct()
    {
        $this->logPath = $this->getEnv('LOG_PATH');
        $this->logLevel = $this->getEnv('LOG_LEVEL');
        $this->redisHost = $this->getEnv('REDIS_HOSTNAME');
        $this->redisPort = $this->getEnv('REDIS_PORT');
        $this->redisQueueName = $this->getEnv('REDIS_QUEUE');

        if (null == $this->redisHost) {
            $this->redisHost = self::DEFAULT_REDIS_HOST;
        }

        if (null == $this->redisPort) {
            $this->redisPort = self::DEFAULT_REDIS_PORT;
        }

        if (null == $this->redisQueueName) {
            $this->redisQueueName = self::DEFAULT_REDIS_QUEUE;
        }

        if (null == $this->logPath) {
            $this->logPath = sys_get_temp_dir().'/'.self::DEFAULT_FILE_LOG;
        }

        if (null == $this->logLevel) {
            $this->logLevel = Logger::WARNING;
        }
    }

    /**
     * @return string
     */
    public function getLogPath()
    {
        return $this->logPath;
    }

    /**
     * @return int
     */
    public function getLogLevel()
    {
        return $this->logLevel;
    }

    /**
     * @return string
     */
    public function getRedisHost()
    {
        return $this->redisHost;
    }

    /**
     * @return string
     */
    public function getRedisPort()
    {
        return $this->redisPort;
    }

    /**
     * @return string
     */
    public function getRedisQueueName()
    {
        return $this->redisQueueName;
    }

    private function getEnv($envName)
    {
        $env = getenv($envName);
        if (null !== $env && !empty($env)) {
            return trim($env);
        }

        return null;
    }
} 