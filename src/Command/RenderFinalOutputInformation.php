<?php

namespace Liuggio\Fastest\Command;


use Liuggio\Fastest\Process\Processes;
use Liuggio\Fastest\Queue\QueueInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class RenderFinalOutputInformation
{

    public function render(OutputInterface $output, Processes $processes)
    {
        $output->writeln($processes->getErrorOutput());
    }
} 