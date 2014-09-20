<?php

namespace Liuggio\Fastest\Command;

use Liuggio\Fastest\Process\Processes;
use Symfony\Component\Console\Output\OutputInterface;

class RenderFinalOutputInformation
{

    public function render(OutputInterface $output, Processes $processes)
    {
        $output->writeln('');
        $output->writeln($processes->getErrorOutput());
    }
}
