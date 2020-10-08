<?php

namespace Liuggio\Fastest\UI;

use Liuggio\Fastest\Process\Processes;
use Liuggio\Fastest\Queue\QueueInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VerboseRenderer implements RendererInterface
{
    /**
     * @var int
     */
    private $messageInTheQueue;

    /**
     * @var int
     */
    private $lastIndex;

    /**
     * @var OutputInterface
     */
    private $output;

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
        $this->messageInTheQueue = $messageInTheQueue;
        $this->errorsSummary = $errorsSummary;
        $this->output = $output;
        $this->lastIndex = 0;
    }

    public function renderHeader(QueueInterface $queue): void
    {
    }

    public function renderFooter(QueueInterface $queue, Processes $processes): void
    {
        $this->renderBody($queue, $processes);
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

    public function renderBody(QueueInterface $queue, Processes $processes): int
    {
        $errorCount = $processes->countErrors();

        $log = $processes->getReport();
        $count = count($log);
        $tests = array_slice($log, $this->lastIndex, $count, true);

        foreach ($tests as $report) {
            ++$this->lastIndex;
            $processorN = '';
            if (OutputInterface::VERBOSITY_VERY_VERBOSE <= $this->output->getVerbosity()) {
                $str = '%d';
                if ($report->isFirstOnThread()) {
                    $str = '<info>%d</info>';
                }
                $processorN = sprintf($str."\t", $report->getProcessorNumber());
            }

            $flag = '<info>✔</info>';
            $err = '';
            if (!$report->isSuccessful()) {
                $flag = '<error>✘</error>';
                if (OutputInterface::VERBOSITY_VERY_VERBOSE <= $this->output->getVerbosity()) {
                    $err = $report->getErrorBuffer();
                }
            }

            $remaining = sprintf('%d/%d', $this->lastIndex, $this->messageInTheQueue);
            $time = $this->formatDuration(round($report->getTime() * 1000));
            // add a tab to add some space for longer strings so that the next column doesn't jump
            if (strlen($time) < 8) {
                $time .= "\t";
            }
            $this->output->writeln($processorN.$remaining."\t".$flag."\t".$time."\t".$report->getSuite().$err);
        }
        $this->lastIndex = $count;

        return $errorCount;
    }

    /**
     * Method to format duration to human readable format.
     *
     * @param float $milliseconds
     *
     * @return string
     */
    private function formatDuration(float $milliseconds): string
    {
        $minutes = floor($milliseconds / 1000 / 60);
        $milliseconds -= ($minutes * 60 * 1000);

        $seconds = floor($milliseconds / 1000);
        $milliseconds -= ($seconds * 1000);

        $values = [
            'min' => $minutes,
            's' => $seconds,
            'ms' => $milliseconds,
        ];

        $parts = [];

        foreach ($values as $text => $value) {
            if ($value > 0) {
                $parts[] = $value.' '.$text;
            }
        }

        return implode(' ', $parts);
    }
}
