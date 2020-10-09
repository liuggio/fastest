<?php

namespace Liuggio\Fastest\Queue;

interface QueueInterface
{
    /**
     * @deprecated Will be removed in v2
     */
    public function pop(): ?TestSuite;

    public function shift(): ?TestSuite;

    /**
     * Push a collections of tests.
     *
     * @phpstan-ignore-next-line // @todo remove in v2 and include return type
     */
    public function push(TestsQueue $tests);

    /**
     * Get the number of the message sent.
     */
    public function getNumberOfPushedMessage(): int;

    /**
     * Get the number of messages..
     */
    public function count(): int;

    public function isEmpty(): bool;

    public function close(): void;
}
