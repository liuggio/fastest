<?php

namespace Liuggio\Fastest\UI;

use Liuggio\Fastest\Process\Processes;
use Liuggio\Fastest\Queue\QueueInterface;

interface RendererInterface
{
    public function renderHeader(QueueInterface $queue): void;

    public function renderBody(QueueInterface $queue, Processes $processes): int;

    public function renderFooter(QueueInterface $queue, Processes $processes): void;
}
