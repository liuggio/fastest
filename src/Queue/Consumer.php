<?php
namespace Liuggio\Fastest\Queue;

/**
 * Get a TestSuite from the queue
 */
class Consumer
{
    protected $queueFactory;

    public function __construct(QueueFactoryInterface $queueFactory)
    {
        $this->queueFactory = $queueFactory;
    }

    public function execute()
    {
        $queue = $this->queueFactory->createForConsumer();

        return $queue->pop();
    }
}
