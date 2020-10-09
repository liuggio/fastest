<?php

namespace Liuggio\Fastest\Queue\Infrastructure;

use Liuggio\Fastest\Queue\QueueInterface;
use Liuggio\Fastest\Queue\TestsQueue;
use Liuggio\Fastest\Queue\TestSuite;

class InMemoryQueue implements QueueInterface
{
    /**
     * @var array<int, TestSuite>
     */
    private $queue;

    public function __construct()
    {
        $this->queue = [];
    }

    public function pop(): ?TestSuite
    {
        return array_pop($this->queue);
    }

    public function shift(): ?TestSuite
    {
        return array_shift($this->queue);
    }

    public function push(TestsQueue $testSuite): bool
    {
        $this->queue = array_merge($this->queue, $testSuite->toArray());

        return count($this->queue) > 0;
    }

    public function getNumberOfPushedMessage(): int
    {
        return count($this->queue);
    }

    public function close(): void
    {
        $this->queue = [];
    }

    public function count(): int
    {
        if (null !== $this->queue) {
            return count($this->queue);
        }

        return 0;
    }

    public function isEmpty(): bool
    {
        return 0 == $this->count();
    }
}
