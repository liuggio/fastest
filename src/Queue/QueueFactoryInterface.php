<?php

namespace Liuggio\Fastest\Queue;

interface QueueFactoryInterface
{
    /**
     * @return QueueInterface
     */
    public function create(): QueueInterface;
}
