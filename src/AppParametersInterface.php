<?php

namespace Liuggio\Fastest;

interface AppParametersInterface
{
    CONST DEFAULT_REDIS_HOST = '127.0.0.1';
    CONST DEFAULT_REDIS_PORT = '6379';
    CONST DEFAULT_REDIS_QUEUE = 'fastest';
    /**
     * @return string
     */
    public function getLogPath();

    /**
     * @return int
     */
    public function getLogLevel();

    /**
     * @return string
     */
    public function getRedisHost();

    /**
     * @return string
     */
    public function getRedisPort();

    /**
     * @return string
     */
    public function getRedisQueueName();
} 