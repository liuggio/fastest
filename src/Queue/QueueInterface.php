<?php

namespace Liuggio\Fastest\Queue;

use Liuggio\Fastest\InputLine;

interface QueueInterface
{
    /**
     * Adds an element to the queue.
     *
     * @param InputLine $value <p>
     *                         The value to enqueue.
     *                         </p>
     */
    public function enqueue(InputLine $value);

    /**
     * Dequeues a node from the queue.
     *
     * @return InputLine|null The value of the dequeued node or null if the queue is empty.
     */
    public function dequeue();

    /**
     * Randomizes and return a new QueueInterface.
     *
     * @return static
     */
    public function randomize();

    /**
     * Checks if the queue is freezed.
     *
     * @return bool whether the heap is empty.
     */
    public function isFrozen();

    /**
     * Freeze the queue for future write access.
     *
     * @return bool whether the heap is empty.
     */
    public function freeze();

    /**
     * Count elements of a queue.
     *
     * @return int queue items count as an integer.
     */
    public function count();
}
