<?php

namespace Liuggio\Fastest\Queue;

use Liuggio\Fastest\Exception\EnqueueIsNotPossibleQueueIsFrozenException;
use Liuggio\Fastest\InputLine;

class SplQueue implements QueueInterface
{
    /**
     * @var \SplQueue
     */
    private $queue;

    /**
     * @var bool
     */
    private $isFrozen = false;

    /**
     * {@inheritdoc}
     */
    public function __construct(\SplQueue $queue = null)
    {
        $this->queue = $queue ?: new \SplQueue(\SplDoublyLinkedList::IT_MODE_FIFO & \SplDoublyLinkedList::IT_MODE_DELETE);
    }

    /**
     * {@inheritdoc}
     */
    public function enqueue(InputLine $value)
    {
        $this->assertIsNotFrozen();
        $this->queue->enqueue($value);
    }

    /**
     * {@inheritdoc}
     */
    public function dequeue()
    {
        try {
            $value = $this->queue->dequeue();

            return $value;
        } catch (\Exception $e) {
            // do nothing
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function randomize()
    {
        $randomizedArray = array();
        for ($this->queue->rewind(); $this->queue->valid(); $this->queue->next()) {
            $randomizedArray[] = $this->queue->current();
        }

        shuffle($randomizedArray);

        $newQueue = new self();
        foreach ($randomizedArray as $item) {
            $newQueue->enqueue($item);
        }

        return $newQueue;
    }

    /**
     * {@inheritdoc}
     */
    public function isFrozen()
    {
        return $this->isFrozen;
    }

    /**
     * {@inheritdoc}
     */
    public function freeze()
    {
        $this->isFrozen = true;
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->queue->count();
    }

    /**
     * @throws EnqueueIsNotPossibleQueueIsFrozenException
     */
    private function assertIsNotFrozen()
    {
        if ($this->isFrozen()) {
            throw new EnqueueIsNotPossibleQueueIsFrozenException();
        }
    }
}
