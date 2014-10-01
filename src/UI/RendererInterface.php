<?php

namespace Liuggio\Fastest\UI;

use Liuggio\Fastest\Process\Processes;
use Liuggio\Fastest\Queue\QueueInterface;

interface RendererInterface
{
    public function renderHeader(QueueInterface $queue);
    public function renderBody(QueueInterface $queue, Processes $processes);
    public function renderFooter(QueueInterface $queue, Processes $processes);
}
