<?php

namespace Liuggio\Fastest\Queue\Infrastructure;

use Liuggio\Fastest\Queue\PopQueueInterface;
use Liuggio\Fastest\Queue\PushQueueInterface;
use Liuggio\Fastest\Queue\TestSuite;

class PredisQueue implements PopQueueInterface, PushQueueInterface
{
    const DEFAULT_QUEUE_NAME = 'TEST_QUEUE';
    private $connection;
    private $parameters;
    private $options;
    private $queueName;

    public function __construct($queueName = self::DEFAULT_QUEUE_NAME, $redisHost = '127.0.0.1', $redisPort = '6379', $redisDB = 1, $options = null)
    {
        $this->queueName = $queueName;
        $this->connection = false;
        $this->options = $options;

        if (null == $this->parameters) {
            $this->parameters = array(
                'host'     => $redisHost,
                'port'     => $redisPort,
                'database' => $redisDB
            );
        }
    }

    private function connect()
    {
        if ($this->isConnected()) {
            return $this->connection;
        }

        $this->connection = new \Predis\Client($this->parameters, $this->options);
    }

    private function isConnected()
    {
        return $this->connection;
    }

    /**
     * @return TestSuite
     */
    public function pop()
    {
        $this->connect();

        return $this->connection->lpop($this->queueName);
    }

    /**
     * @param TestSuite $testSuite
     *
     * @return bool
     */
    public function push(TestSuite $testSuite)
    {
        $this->connect();

        return $this->connection->rpush($this->queueName, $testSuite);
    }
} 