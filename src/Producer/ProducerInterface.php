<?php

namespace Liuggio\Fastest\Producer;

use Liuggio\Fastest\Queue\QueueInterface;

interface ProducerInterface
{
    /**
     * Starts producing new CommandLines into the queue.
     *
     * @param QueueInterface $queue
     */
    public function produce(QueueInterface $queue);
}
