<?php

namespace Liuggio\Fastest\Command;

use Liuggio\Fastest\Process\Processes;
use Liuggio\Fastest\Queue\QueueInterface;
use Liuggio\Fastest\Queue\TestsQueue;
use Liuggio\Fastest\UI\ProgressBarRenderer;
use Liuggio\Fastest\UI\VerboseRenderer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Liuggio\Fastest\Process\ProcessesManager;
use Liuggio\Fastest\Process\ProcessFactory;
use Liuggio\Fastest\Process\ProcessorCounter;
use Liuggio\Fastest\Queue\Infrastructure\InMemoryQueueFactory;
use Liuggio\Fastest\Queue\ReadFromInputAndPushIntoTheQueue;
use Symfony\Component\Stopwatch\Stopwatch;

class ParallelCommand extends Command
{
    private const EXECUTE_OPTION = 'execute';
    private const PROCESS_OPTION = 'process';
    private const BEFORE_OPTION = 'before';
    private const XML_OPTION = 'xml';
    private const PRESERVE_ORDER_OPTION = 'preserve-order';
    private const RERUN_FAILED_OPTION = 'rerun-failed';
    private const NO_ERRORS_SUMMARY_OPTION = 'no-errors-summary';

    protected function configure(): void
    {
        $this
            ->setName('fastest')
            ->setDescription('Consume the element parallel.')
            ->addArgument(
                self::EXECUTE_OPTION,
                InputArgument::OPTIONAL,
                'Optional command to execute.'
            )
            ->addOption(
                self::PROCESS_OPTION,
                'p',
                InputOption::VALUE_REQUIRED,
                'Number of parallel processes, default: available CPUs.'
            )
            ->addOption(
                self::BEFORE_OPTION,
                'b',
                InputOption::VALUE_REQUIRED,
                'Execute a process before consuming the queue, it executes this command once per process, useful for init schema and load fixtures.'
            )
            ->addOption(
                self::XML_OPTION,
                'x',
                InputOption::VALUE_REQUIRED,
                'Read input from a phpunit xml file from the \'<testsuites>\' collection. Note: it is not used for consuming.'
            )
            ->addOption(
                self::PRESERVE_ORDER_OPTION,
                'o',
                InputOption::VALUE_NONE,
                'Queue is randomized by default, with this option the queue is read preserving the order.'
            )
            ->addOption(
                self::RERUN_FAILED_OPTION,
                'r',
                InputOption::VALUE_NONE,
                'Re-run failed test with before command if exists.'
            )
            ->addOption(
                self::NO_ERRORS_SUMMARY_OPTION,
                null,
                InputOption::VALUE_NONE,
                'Do not display all errors after the test run. Useful with --vv because it already displays errors immediately after they happen.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $stopWatch = new Stopwatch();
        $stopWatch->start('execute');
        // init
        $queueFactory = new InMemoryQueueFactory();
        $readFromInputAndPushIntoTheQueue = new ReadFromInputAndPushIntoTheQueue($queueFactory);

        $xmlOption = $input->getOption(self::XML_OPTION);
        if (!is_string($xmlOption) && null !== $xmlOption) {
            throw new \Exception(sprintf('%s should have a scalar (string) or null value', self::XML_OPTION));
        }

        $preserveOrderOption = $input->getOption(self::PRESERVE_ORDER_OPTION);
        if (!is_bool($preserveOrderOption) && null !== $preserveOrderOption) {
            throw new \Exception(sprintf('%s should not have any value', self::PRESERVE_ORDER_OPTION));
        }
        $preserveOrderOption = (bool) $preserveOrderOption;

        $queue = $readFromInputAndPushIntoTheQueue->execute($xmlOption, $preserveOrderOption);

        $processOption = $input->getOption(self::PROCESS_OPTION);
        if ((!is_numeric($processOption) || !is_int((int) $processOption)) && null !== $processOption) {
            throw new \Exception(sprintf('%s should have an integer value', self::PROCESS_OPTION));
        }
        $processOption = (int) $processOption;

        $maxNumberOfParallelProc = $this->getMaxNumberOfProcess($processOption);
        $executeOption = $input->getArgument(self::EXECUTE_OPTION);
        if (!is_string($executeOption) && null !== $executeOption) {
            throw new \Exception(sprintf('%s should have a scalar (string) or null value', self::EXECUTE_OPTION));
        }

        $processFactory = new ProcessFactory($maxNumberOfParallelProc, $executeOption);

        $beforeOption = $input->getOption(self::BEFORE_OPTION);
        if (!is_string($beforeOption) && null !== $beforeOption) {
            throw new \Exception(sprintf('%s should have a scalar (string) or null value', self::BEFORE_OPTION));
        }

        $processManager = new ProcessesManager($maxNumberOfParallelProc, $processFactory, $beforeOption);

        // header
        $shuffled = $input->getOption(self::PRESERVE_ORDER_OPTION) ? '' : 'shuffled ';
        $output->writeln('- <fg=white;bg=blue>'.$queue->count().'</> '.$shuffled.'test classes into the queue.');
        $output->writeln('- Will be consumed by <fg=white;bg=blue>'.$maxNumberOfParallelProc.'</> parallel Processes.');

        // loop
        $processes = $this->doExecute($input, $output, $queue, $processManager);

        $event = $stopWatch->stop('execute');
        $output->writeln(sprintf(
            '    Time: %s, Memory: %s',
            $this->formatDuration((int) $event->getDuration()),
            $this->formatMemory($event->getMemory())
        ));

        if ($input->getOption(self::RERUN_FAILED_OPTION)) {
            $processes = $this->executeBeforeCommand($queue, $processes, $input, $output, $processManager);
        }

        return $processes->getExitCode();
    }

    private function getMaxNumberOfProcess(int $maxNumberOfParallelProc): int
    {
        if ($maxNumberOfParallelProc > 0) {
            return $maxNumberOfParallelProc;
        }

        $processorCounter = new ProcessorCounter();

        return $processorCounter->execute();
    }

    private function doExecute(
        InputInterface $input,
        OutputInterface $output,
        QueueInterface $queue,
        ProcessesManager $processManager
    ): Processes {
        $processes = null;

        if ($this->isVerbose($output)) {
            $progressBar = new VerboseRenderer($queue->count(), $this->hasErrorSummary($input), $output);
        } else {
            $progressBar = new ProgressBarRenderer($queue->count(), $this->hasErrorSummary($input), $output);
        }

        $progressBar->renderHeader($queue);

        while ($processManager->assertNProcessRunning($queue, $processes)) {
            $progressBar->renderBody($queue, $processes);
        }

        /*
         * @var Processes $processes
         */
        $processes->cleanUP(); //it is not getting called with -p1 after the last process otherwise
        $processes->wait(function () use ($progressBar, $queue, $processes) {
            $progressBar->renderBody($queue, $processes);
        });
        $progressBar->renderFooter($queue, $processes);

        return $processes;
    }

    private function isVerbose(OutputInterface $output): bool
    {
        return OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity();
    }

    private function hasErrorSummary(InputInterface $input): bool
    {
        return !$input->getOption(self::NO_ERRORS_SUMMARY_OPTION);
    }

    private function executeBeforeCommand(
        QueueInterface $queue,
        Processes $processes,
        InputInterface $input,
        OutputInterface $output,
        ProcessesManager $processManager
    ): Processes {
        if (!$processes->isSuccessful()) {
            $array = $processes->getErrorOutput();
            $output->writeln(sprintf('Re-Running [%d] elements', count($array)));
            $queue->push(new TestsQueue(array_keys($array)));
            $processes = $this->doExecute($input, $output, $queue, $processManager);
        }

        return $processes;
    }

    /**
     * Method to format duration to human readable format.
     */
    private function formatDuration(int $milliseconds): string
    {
        $hours = floor($milliseconds / 1000 / 3600);
        $milliseconds -= ($hours * 3600 * 1000);

        $minutes = floor($milliseconds / 1000 / 60);
        $milliseconds -= ($minutes * 60 * 1000);

        $seconds = floor($milliseconds / 1000);
        $milliseconds -= ($seconds * 1000);

        $values = [
            'hour' => $hours,
            'minute' => $minutes,
            'second' => $seconds,
            'millisecond' => $milliseconds,
        ];

        $parts = [];

        foreach ($values as $text => $value) {
            if ($value > 0) {
                $parts[] = $value.' '.$text.($value > 1 ? 's' : '');
            }
        }

        return implode(' ', $parts);
    }

    /**
     * Method to format memory usage to human readable format.
     *
     * @param int $bytes
     *
     * @return string
     */
    private function formatMemory(int $bytes): string
    {
        $units = ['B', 'KiB', 'MiB', 'GiB'];
        $mod = 1024;
        $power = ($bytes > 0) ? (int) floor(log($bytes, $mod)) : 0;

        return sprintf('%01.2f %s', $bytes / pow($mod, $power), $units[$power]);
    }
}
