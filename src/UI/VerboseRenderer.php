<?php

namespace Liuggio\Fastest\UI;

use Liuggio\Fastest\Process\Processes;
use Liuggio\Fastest\Queue\QueueInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VerboseRenderer implements RendererInterface
{
    private $messageInTheQueue;
    private $lastIndex;
    private $output;

    public function __construct($messageInTheQueue, OutputInterface $output)
    {
        $this->messageInTheQueue = $messageInTheQueue;
        $this->output = $output;
        $this->lastIndex = 0;
    }

    public function renderHeader(QueueInterface $queue)
    {
    }

    public function renderFooter(QueueInterface $queue, Processes $processes)
    {
        $this->renderBody($queue, $processes);
        $this->output->writeln('');
        $this->output->writeln($processes->getErrorOutput());

        $out = "    <info>✔</info> You are great!";
        if (!$processes->isSuccessful()) {
            $out = "    <error>✘ ehm broken tests...</error>";
        }

        $this->output->writeln(PHP_EOL.$out);
    }

    public function renderBody(QueueInterface $queue, Processes $processes)
    {
        $errorCount = $processes->countErrors();

        $log = $processes->getReport();
        $count = count($log);
        $tests = array_slice($log, $this->lastIndex, $count, 1);

        foreach ($tests as $report) {
            $this->lastIndex++;
            $processorN = "";
            if (OutputInterface::VERBOSITY_VERY_VERBOSE <= $this->output->getVerbosity()) {
                $str = '%d';
                if ($report->isFirstOnThread()) {
                    $str = "<info>%d</info>";
                }
                $processorN = sprintf($str."\t", $report->getProcessorNumber());
            }

            $flag = "<info>✔</info>";

            $skipped = '';
            if ($report->getSkippedCount() > 0) {
                $flag = "<info>S</info>";
                if (OutputInterface::VERBOSITY_VERY_VERBOSE <= $this->output->getVerbosity()) {
                    $skipped = "<comment>\n|\n|_ {$report->getSkippedCount()} Test skipped</comment>\n\n";
                }
            }
            $err = '';
            if (!$report->isSuccessful()) {
                $flag = "<error>✘</error>";
                if (OutputInterface::VERBOSITY_VERY_VERBOSE <= $this->output->getVerbosity()) {
                    $err = $report->getErrorBuffer();
                }
            }

            $remaining = sprintf('%d/%d', $this->lastIndex, $this->messageInTheQueue);
            $this->output->writeln($processorN.$remaining."\t".$flag."\t".$report->getSuite().$err.$skipped);
        }
        $this->lastIndex = $count;

        return $errorCount;
    }
}
