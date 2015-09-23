<?php

namespace Liuggio\Fastest\Queue;

use Liuggio\Fastest\Event\FrozenQueueEvent;
use Liuggio\Fastest\InputLine;
use Liuggio\Fastest\Event\InputLineDequeuedEvent;
use Liuggio\Fastest\Event\InputLineEnqueuedEvent;
use Liuggio\Fastest\Event\EventsName;
use Liuggio\Fastest\Event\EmptiedQueueEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EventDispatcherQueue implements QueueInterface
{
    /** @var  QueueInterface */
    private $queue;
    /** @var  EventDispatcherInterface */
    private $eventDispatcher;

    /**
     * EventDispatcherQueue constructor.
     *
     * @param QueueInterface           $queue
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher = null, QueueInterface $queue = null)
    {
        $this->queue = $queue ?: new SplQueue();
        $this->eventDispatcher = $eventDispatcher ?: new EventDispatcher();
    }

    /**
     * {@inheritdoc}
     */
    public function enqueue(InputLine $value)
    {
        $this->queue->enqueue($value);
        $this->eventDispatcher->dispatch(EventsName::INPUT_LINE_ENQUEUED, new InputLineEnqueuedEvent($value));
    }

    /**
     * {@inheritdoc}
     */
    public function dequeue()
    {
        $commandLine = $this->queue->dequeue();
        if (null === $commandLine) {
            $this->eventDispatcher->dispatch(EventsName::QUEUE_IS_EMPTY, new EmptiedQueueEvent());

            return;
        }

        $this->eventDispatcher->dispatch(EventsName::INPUT_LINE_DEQUEUED, new InputLineDequeuedEvent($commandLine));

        return $commandLine;
    }

    /**
     * {@inheritdoc}
     */
    public function randomize()
    {
        $newQueue = $this->queue->randomize();

        return new self($this->eventDispatcher, $newQueue);
    }

    /**
     * {@inheritdoc}
     */
    public function isFrozen()
    {
        return $this->queue->isFrozen();
    }

    /**
     * {@inheritdoc}
     */
    public function freeze()
    {
        if ($this->queue->isFrozen()) {
            return;
        }

        $this->queue->freeze();
        $this->eventDispatcher->dispatch(EventsName::QUEUE_IS_FROZEN, new FrozenQueueEvent());
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->queue->count();
    }
}
