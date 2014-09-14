<?php

namespace Liuggio\Fastest\Queue;

interface PushQueueInterface
{
    /**
     * @param TestSuite $testSuite
     *
     * @return bool
     */
    public function push(TestSuite $testSuite);
} 