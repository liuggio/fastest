<?php

namespace Liuggio\Fastest\Queue\Infrastructure;

use Liuggio\Fastest\Queue\QueueInterface;
use Liuggio\Fastest\Queue\TestsQueue;
use Liuggio\Fastest\Queue\TestSuite;

class RedisQueue implements QueueInterface
{
    private $queueName;
    private $connection;
    private $numberOfPushedMessage;
    private $options;
    private $parameters;
    const DEFAULT_QUEUE_NAME ='fastest_001';

    public function __construct($queueName = self::DEFAULT_QUEUE_NAME, $checkEmptyQeue = false, $redisHost = '127.0.0.1', $redisPort = '6379', $redisDB = 1, $options = null)
    {
        $this->connection = false;
        $this->options = $options;
        $this->numberOfPushedMessage = 0;
        if (null == $this->parameters) {
            $this->parameters = array(
                'host' => $redisHost,
                'port' => $redisPort,
                'database' => $redisDB
            );
        }
        $this->connect();
        $this->queueName = $queueName;
        if ($checkEmptyQeue) {
            $this->queueName = $this->getAnEmptyQueueName($queueName);
        }
    }

    private function isConnected()
    {
        return $this->connection;
    }

    private function connect()
    {
        if ($this->isConnected()) {
            return $this->connection;
        }
        $this->connection = new \Predis\Client($this->parameters, $this->options);
    }

    public function pop()
    {
        $this->connect();


        if ($this->getMessagesInTheQueue()) {
            return trim($this->connection->lpop($this->queueName));
        }

        return false;

    }

    public function getMessagesInTheQueue($port = null)
    {
        if (null === $port) {
            $port = $this->queueName;
        }

        return (int) $this->connection->llen($port);
    }

    public function push(TestsQueue $testsQueue)
    {
        $fifo = $this;
        $array = $testsQueue->toArray();
        $counter = 0;
        array_walk($array, function ($item) use ($fifo, $counter) {
            if ($fifo->pushOne($item)) {
                $this->incrementNumberOfPushedMessage();
                $counter++;
            }
        });

        return $counter;
    }

    /**
     * @return int
     */
    public function getNumberOfPushedMessage()
    {
        return $this->numberOfPushedMessage;
    }

    /**
     * @return mixed
     */
    public function getQueuePort()
    {
        return $this->queueName;
    }

    /**
     * @return bool
     */
    public function close()
    {
        $this->connection->lrem($this->queueName, 0, -1);
    }

    private function incrementNumberOfPushedMessage($by = 1)
    {
        $this->numberOfPushedMessage += $by;
    }

    private function pushOne(TestSuite $testSuite)
    {
        $this->connect();
        return $this->connection->rpush($this->queueName, $testSuite);
    }

    private function queueExists($name)
    {
        $this->connect();
        return ($this->getMessagesInTheQueue($name) > 0);
    }
    /**
     * @throws \Exception
     */
    private function getAnEmptyQueueName($queueName, $maxTries = 10)
    {
        $tries = 0;

        while ($this->queueExists($queueName) && $tries < $maxTries) {
            $queueName = 'fastest_'.rand();
            $tries++;
        }
        if ($tries >= $maxTries) {
            throw new \Exception('impossible to create a new queue');
        }

        return $queueName;
    }
}
