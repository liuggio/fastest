<?php

namespace Liuggio\Fastest\UI;

use Liuggio\Fastest\Process\Processes;
use Liuggio\Fastest\Queue\QueueInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NoProgressRenderer implements RendererInterface
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var bool
     */
    private $errorsSummary;

    /**
     * @param bool $errorsSummary Whether to display errors summary in the footer
     * @param OutputInterface $output
     */
    public function __construct(bool $errorsSummary, OutputInterface $output)
    {
        $this->errorsSummary = $errorsSummary;
        $this->output = $output;
    }

    public function renderHeader(QueueInterface $queue): void
    {
    }

    public function renderBody(QueueInterface $queue, Processes $processes): int
    {
        return $processes->countErrors();
    }

    public function renderFooter(QueueInterface $queue, Processes $processes): void
    {
        $this->output->writeln('');
        if ($this->errorsSummary) {
            $this->output->writeln($processes->getErrorOutput());
        }

        $out = '    <info>✔</info> You are great!';
        if (!$processes->isSuccessful()) {
            $out = '    <error>✘ ehm broken tests...</error>';
        }

        $this->output->writeln(PHP_EOL . $out);
    }
}
