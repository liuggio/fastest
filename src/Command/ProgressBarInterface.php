<?php
namespace Liuggio\Fastest\Command;

use Liuggio\Fastest\Process\Processes;

interface ProgressBarInterface
{
    public function render($queue, Processes $processes);

    public function finish($queue, Processes $processes);
}
