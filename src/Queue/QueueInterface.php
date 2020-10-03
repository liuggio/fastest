<?php

namespace Liuggio\Fastest\Queue;

interface QueueInterface
{
    /**
     * @return TestSuite
     *
     * @deprecated Will be removed in v2
     */
    public function pop();

    /**
     * @return TestSuite
     */
    public function shift();

    /**
     * Push a collections of tests.
     *
     * @param TestsQueue $tests
     */
    public function push(TestsQueue $tests);

    /**
     * Get the number of the message sent.
     *
     * @return int
     */
    public function getNumberOfPushedMessage();

    /**
     * Get the number of messages..
     *
     * @return int
     */
    public function count();

    /**
     * @return bool
     */
    public function isEmpty();

    public function close();
}
