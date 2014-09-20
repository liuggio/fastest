<?php

namespace Liuggio\Fastest\Command;

use Liuggio\Fastest\Process\Processes;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class UIProgressBar
{
    private $bar;
    private $last;

    public function __construct($messageInTheQueue, OutputInterface $output)
    {
        $output->writeln('');
        $output->writeln('');
        $output->writeln('');
        $output->writeln('');
        $this->bar = new ProgressBar($output, $messageInTheQueue);
        $this->bar->setFormat('very_verbose');
        $this->bar->setFormat("%current%/%max% <fg=white;bg=blue>[%bar%]</> %percent:3s%% %elapsed:6s% %memory:6s% \n\n     %number%");
        $this->bar->setMessage('<info>0</info> failures', 'number');
        $this->bar->setMessage('', 'latestA');
        $this->bar->setMessage('', 'latestB');
        $this->last = $messageInTheQueue;
        $this->bar->start();
    }

    public function render($queue, Processes $processes)
    {
        $now = $queue->count();
        $errorCount = $processes->countErrors();

        if ($errorCount > 0) {
            $this->bar->setFormat("%current%/%max% <fg=white;bg=blue>[%bar%]</> %percent:3s%% %elapsed:6s% %memory:6s% \n\n     %number%S");
            $this->bar->setMessage(sprintf("<error>%d</error> failures.", $errorCount), 'number');
        }

        if ($this->last != $now) {
            $this->bar->advance($this->last-$now);
        }

        $this->last = $now;

        return $errorCount;
    }

    public function finish($queue, Processes $processes)
    {
        $this->render($queue, $processes);
        $this->bar->finish();
    }
}
