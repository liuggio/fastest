<?php

namespace Liuggio\Fastest\UI;

use Liuggio\Fastest\Process\Processes;
use Liuggio\Fastest\Queue\QueueInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DotProgressRenderer implements RendererInterface
{
    /**
     * @var int
     */
    private $messageInTheQueue;

    /**
     * @var bool
     */
    private $errorsSummary;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var int
     */
    private $lastIndex;

    /**
     * @var int
     */
    private $dotsInLine;

    /**
     * @param int $messageInTheQueue
     * @param bool $errorsSummary Whether to display errors summary in the footer
     * @param OutputInterface $output
     * @param int $dotsInLine
     */
    public function __construct(
        int $messageInTheQueue,
        bool $errorsSummary,
        OutputInterface $output,
        int $dotsInLine = 70
    ) {
        $this->messageInTheQueue = $messageInTheQueue;
        $this->errorsSummary = $errorsSummary;
        $this->output = $output;
        $this->lastIndex = 0;
        $this->dotsInLine = $dotsInLine;
    }

    public function renderHeader(QueueInterface $queue): void
    {
        $this->output->writeln('');
        $this->output->writeln('');
    }

    public function renderBody(QueueInterface $queue, Processes $processes): int
    {
        $errorCount = $processes->countErrors();
        $reports = $processes->getReport();
        $count = count($reports);

        for ($index = $this->lastIndex; $index < $count; ++$index) {
            $this->output->write($reports[$index]->isSuccessful() ? '.' : '<error>F</error>');
            $step = $index + 1;

            if (0 === $step % $this->dotsInLine) {
                $this->output->writeln(' '.$step.'/'.$this->messageInTheQueue);
            }
        }

        $this->lastIndex = $count;

        return $errorCount;
    }

    public function renderFooter(QueueInterface $queue, Processes $processes): void
    {
        $this->renderBody($queue, $processes);

        if (0 !== $this->lastIndex % $this->dotsInLine) {
            $this->output->writeln(' '.$this->lastIndex.'/'.$this->messageInTheQueue);
        }

        $this->output->writeln('');

        $errorCount = $processes->countErrors();
        if ($errorCount > 0) {
            $this->output->writeln(sprintf('     <error>%d</error> failures.', $errorCount));
            $this->output->writeln('');
        }

        if ($this->errorsSummary) {
            $this->output->writeln($processes->getErrorOutput());
        }

        $out = '    <info>✔</info> You are great!';
        if (!$processes->isSuccessful()) {
            $out = '    <error>✘ ehm broken tests...</error>';
        }

        $this->output->writeln(PHP_EOL.$out);
    }
}
