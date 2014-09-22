<?php

namespace Liuggio\Fastest\Command;

use Liuggio\Fastest\Process\Processes;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Console\Output\OutputInterface;

class UIProgressBar
{
    private $bar;
    private $last;
    private $degrade;
    private $output;
    private $preProcesses;

    public function __construct($messageInTheQueue, OutputInterface $output, $helper, $preProcesses = 0)
    {
        $this->preProcesses = (int) $preProcesses;

        $this->output = $output;
        $this->degrade = true;
        $this->output->writeln('');
        $this->output->writeln('');
        $this->output->writeln('');
        $this->output->writeln('');

        if (class_exists('\Symfony\Component\Console\Helper\ProgressBar')) {
            $this->bar = new ProgressBar($output, $messageInTheQueue+$this->preProcesses );
            $this->bar->setFormat('very_verbose');
            $this->bar->setFormat("%current%/%max% <fg=white;bg=blue>[%bar%]</> %percent:3s%% %elapsed:6s% %memory:6s% \n\n     %number%");
            $this->bar->start();
            $this->degrade = false;
        } else {
            $this->bar = $helper;
            $this->bar->setFormat('very_verbose');
            $this->bar->setFormat(ProgressHelper::FORMAT_VERBOSE);
            $this->bar->setBarCharacter('<fg=white;bg=blue>=</>');
            $this->bar->start($output, $messageInTheQueue+$this->preProcesses );
        }


        $this->writeMessage('<info>0</info> failures', 'number');
        $this->last = $messageInTheQueue+$this->preProcesses;

    }

    public function render($queue, Processes $processes)
    {
        $now = $queue->count();
        $errorCount = $processes->countErrors();

        if ($errorCount > 0) {
            $this->bar->setBarCharacter('<error>=</error>');
            $this->writeMessage(sprintf("<error>%d</error> failures.", $errorCount), 'number');
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



    private function writeMessage($message, $what)
    {
        if (!$this->degrade) {
            $this->bar->setMessage($what, $message);
            return true;
        }
    }
}
