<?php

namespace Liuggio\Fastest\UI;

use Liuggio\Fastest\Process\Processes;
use Liuggio\Fastest\Queue\QueueInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class ProgressBarRenderer implements RendererInterface
{
    /**
     * @var ProgressBar
     */
    private $bar;

    /**
     * @var int
     */
    private $last;

    /**
     * @var bool
     */
    private $degrade;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var int
     */
    private $messagesInTheQueue;

    /**
     * @var bool
     */
    private $errorsSummary;

    /**
     * @param int $messageInTheQueue
     * @param bool $errorsSummary Whether to display errors summary in the footer
     * @param OutputInterface $output
     */
    public function __construct(int $messageInTheQueue, bool $errorsSummary, OutputInterface $output)
    {
        $this->messagesInTheQueue = $messageInTheQueue;
        $this->errorsSummary = $errorsSummary;
        $this->output = $output;
        $this->degrade = true;
        $this->bar = new ProgressBar($this->output, $this->messagesInTheQueue);
    }

    public function renderHeader(QueueInterface $queue): void
    {
        $this->output->writeln('');
        $this->output->writeln('');
        $this->output->writeln('');
        $this->output->writeln('');

        $this->bar->setFormat('very_verbose');
        $this->bar->setFormat("%current%/%max% <fg=white;bg=blue>[%bar%]</> %percent:3s%% %elapsed:6s% %memory:6s% \n\n     %number%");
        $this->bar->start();
        $this->degrade = false;

        $this->writeMessage('<info>0</info> failures', 'number');
        $this->last = $this->messagesInTheQueue;
    }

    public function renderBody(QueueInterface $queue, Processes $processes): int
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

    public function renderFooter(QueueInterface $queue, Processes $processes): void
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

    private function writeMessage(string $what, string $message): void
    {
        if ($this->degrade) {
            return;
        }

        $this->bar->setMessage($what, $message);
    }
}
