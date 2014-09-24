<?php

namespace Liuggio\Fastest\Command;

use Liuggio\Fastest\Process\Processes;
use Symfony\Component\Console\Output\OutputInterface;

class UIVerboseProgressBar implements ProgressBarInterface
{
    private $messageInTheQueue;
    private $lastIndex;
    private $output;
    private $preProcesses;

    public function __construct($messageInTheQueue, OutputInterface $output, $preProcesses = 0)
    {
        $this->messageInTheQueue = $messageInTheQueue+$preProcesses;
        $this->output = $output;
        $this->lastIndex = 0;
        $this->preProcesses = (int) $preProcesses;
    }

    public function render($queue, Processes $processes)
    {
        $now = $queue->count()+$this->preProcesses;
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
            $err = '';
            if (!$report->isSuccessful()) {
                $flag = "<error>✘</error>";
                if (OutputInterface::VERBOSITY_VERY_VERBOSE <= $this->output->getVerbosity()) {
                    $err = $report->getErrorBuffer();
                }
            }

            $remaining = sprintf('%d/%d', $this->lastIndex, $this->messageInTheQueue);
            $this->output->writeln($processorN.$remaining."\t".$flag."\t".$report->getSuite().$err);
        }
        $this->lastIndex = $count;

        return $errorCount;
    }

    public function finish($queue, Processes $processes)
    {
        $this->render($queue, $processes);
    }
}
