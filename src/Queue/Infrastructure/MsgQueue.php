<?php

namespace Liuggio\Fastest\Queue\Infrastructure;

use Liuggio\Fastest\Queue\QueueInterface;
use Liuggio\Fastest\Queue\TestsQueue;
use Liuggio\Fastest\Queue\TestSuite;

class MsgQueue implements QueueInterface
{
    private $queuePort;
    private $resource;
    private $numberOfPushedMessage;

    public function __construct($queuePort)
    {
        $this->queuePort = (int) $queuePort;
        $this->resource = msg_get_queue($this->queuePort);
        $this->numberOfPushedMessage = 0;
    }

    public function pop()
    {
        $msgTypeReceive = 1;
        $msgTypeEffective = null;
        $maxsize = 999;
        $serializeNeeded = false;
        $data = null;

        if ($this->getMessagesInTheQueue() > 0) {
            if (msg_receive($this->resource, $msgTypeReceive ,$msgTypeEffective, $maxsize, $data, $serializeNeeded, MSG_IPC_NOWAIT, $err)===true) {
                return new TestSuite(trim($data));
            }

            throw new \Exception('msg_receive error'.$err);
        }

        return false;
    }

    public function getMessagesInTheQueue()
    {
        $queueStatus = msg_stat_queue($this->resource);

        return $queueStatus['msg_qnum'];
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
        return $this->queuePort;
    }

    /**
     * @return bool
     */
    public function close()
    {
        if (msg_queue_exists($this->queuePort)) {
           return msg_remove_queue($this->resource);
        }

        return true;
    }

    private function incrementNumberOfPushedMessage($by = 1)
    {
        $this->numberOfPushedMessage += $by;
    }

    private function pushOne(TestSuite $testSuite)
    {
        $serializeNeeded=false;
        $blockSend=true;
        $msgTypeSend=1;
        $err = null;

        if (true === msg_send($this->resource, $msgTypeSend, (string) $testSuite, $serializeNeeded, $blockSend, $err)) {
            return true;
        }

        throw new \Exception('msg_send error'.$err);
    }

    /**
     * @throws \Exception
     */
    public static function createMsgQueueOnThisPort($queuePort)
    {
        if (!msg_queue_exists($queuePort)) {
            throw new \Exception('Queue Not opened.');
        }

        return new self($queuePort);
    }

    /**
     * @throws \Exception
     */
    public static function createMsgQueueOnAnEmptyPort($queuePort, $maxTries = 10)
    {
        $tries = 0;

        while (msg_queue_exists($queuePort) && $tries < $maxTries) {
            $queuePort =rand(100000, 200000);
            $tries++;
        }
        if ($tries >= $maxTries) {
            throw new \Exception('impossible to create a new queue');
        }

        return new self($queuePort);
    }
}
