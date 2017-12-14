<?php

namespace Liuggio\Fastest\UI;

use Liuggio\Fastest\Process\Processes;
use Liuggio\Fastest\Queue\QueueInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Console\Output\OutputInterface;

class ProgressBarRenderer implements RendererInterface
{
    private $bar;
    private $last;
    private $degrade;
    private $output;
    private $messagesInTheQueue;
    private $errorsSummary;

    /**
     * @param $messageInTheQueue
     * @param bool            $errorsSummary Whether to display errors summary in the footer
     * @param OutputInterface $output
     * @param $helper
     */
    public function __construct($messageInTheQueue, $errorsSummary, OutputInterface $output, $helper)
    {
        $this->messagesInTheQueue = $messageInTheQueue;
        $this->errorsSummary = $errorsSummary;
        $this->output = $output;
        $this->helper = $helper;
        $this->degrade = true;
    }

    public function renderHeader(QueueInterface $queue)
    {
        $this->output->writeln('');
        $this->output->writeln('');
        $this->output->writeln('');
        $this->output->writeln('');

        if (class_exists('\Symfony\Component\Console\Helper\ProgressBar')) {
            $this->bar = new ProgressBar($this->output, $this->messagesInTheQueue);
            $this->bar->setFormat('very_verbose');
            $this->bar->setFormat("%current%/%max% <fg=white;bg=blue>[%bar%]</> %percent:3s%% %elapsed:6s% %memory:6s% \n\n     %number%");
            $this->bar->start();
            $this->degrade = false;
        } else {
            $this->bar = $this->helper;
            $this->bar->setFormat('very_verbose');
            $this->bar->setFormat(ProgressHelper::FORMAT_VERBOSE);
            $this->bar->setBarCharacter('<fg=white;bg=blue>=</>');
            $this->bar->start($this->output, $this->messagesInTheQueue);
        }

        $this->writeMessage('<info>0</info> failures', 'number');
        $this->last = $this->messagesInTheQueue;
    }

    public function renderBody(QueueInterface $queue, Processes $processes)
    {
        $now = $queue->count();
        $errorCount = $processes->countErrors();

        if ($errorCount > 0) {
            $this->bar->setBarCharacter('<error>=</error>');
            $this->writeMessage(sprintf('<error>%d</error> failures.', $errorCount), 'number');
        }

        if ($this->last != $now) {
            $this->bar->advance($this->last - $now);
        }

        $this->last = $now;

        return $errorCount;
    }

    public function renderFooter(QueueInterface $queue, Processes $processes)
    {
        $this->renderBody($queue, $processes);
        $this->bar->finish();
        $this->output->writeln('');
        if ($this->errorsSummary) {
            $this->output->writeln($processes->getErrorOutput());
        }

        $out = '    <info>✔</info> You are great!';
        if (!$processes->isSuccessful()) {
            $out = '    <error>✘ ehm broken tests...</error>';
        }

        $this->output->writeln(PHP_EOL.$out);
    }

    private function writeMessage($what, $message)
    {
        if (!$this->degrade) {
            $this->bar->setMessage($what, $message);

            return true;
        }
    }
}
