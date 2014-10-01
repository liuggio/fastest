<?php

namespace Liuggio\Fastest\Command;

use Liuggio\Fastest\Queue\TestsQueue;
use Liuggio\Fastest\UI\ProgressBarRenderer;
use Liuggio\Fastest\UI\VerboseRenderer;
use Symfony\Component\Console\Command\Command;
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
    protected function configure()
    {
        $this
            ->setName('fastest')
            ->setDescription('Consume the element parallel.')
            ->addArgument(
                'execute',
                InputArgument::OPTIONAL,
                'Optional command to execute.'
            )
            ->addOption(
                'process',
                'p',
                InputOption::VALUE_REQUIRED,
                'Number of parallel processes, default: available CPUs.'
            )
            ->addOption(
                'before',
                'b',
                InputOption::VALUE_REQUIRED,
                'Execute a process before consuming the queue, it executes this command once per process, useful for init schema and load fixtures.'
            )
            ->addOption(
                'xml',
                'x',
                InputOption::VALUE_REQUIRED,
                'Read input from a phpunit xml file from the \'<testsuites>\' collection. Note: it is not used for consuming.'
            )
            ->addOption(
                'preserve-order',
                'o',
                InputOption::VALUE_NONE,
                'Queue is randomized by default, with this option the queue is read preserving the order.'
            )
            ->addOption(
                'rerun-failed',
                'r',
                InputOption::VALUE_NONE,
                'Re-run failed test with before command if exists.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $stopWatch = new Stopwatch();
        $stopWatch->start('execute');
        // init
        $queueFactory = new InMemoryQueueFactory();
        $readFromInputAndPushIntoTheQueue = new ReadFromInputAndPushIntoTheQueue($queueFactory);

        $queue = $readFromInputAndPushIntoTheQueue
            ->execute($input->getOption('xml'), $input->getOption('preserve-order'));

        $maxNumberOfParallelProc = $this->getMaxNumberOfProcess($input->getOption('process'));
        $processFactory = new ProcessFactory($maxNumberOfParallelProc, $input->getArgument('execute'));
        $processManager =  new ProcessesManager($processFactory, $maxNumberOfParallelProc, $input->getOption('before'));

        // header
        $shuffled = $input->getOption('preserve-order')?'':'shuffled ';
        $output->writeln('- <fg=white;bg=blue>'.$queue->count().'</> '.$shuffled.'tests into the queue.');
        $output->writeln('- Will be consumed by <fg=white;bg=blue>'.$maxNumberOfParallelProc.'</> parallel Processes.');

        // loop
        $processes = $this->doExecute($output, $queue, $processManager);

        $event =$stopWatch->stop('execute');
        $output->writeln( sprintf("    Time: %d ms, Memory: %d b", $event->getDuration(), $event->getMemory()));

        if ($input->getOption('rerun-failed') ) {
            $processes = $this->executeBeforeCommand($queue, $processes, $output, $processManager);
        }

        return $processes->getExitCode();
    }

    private function getMaxNumberOfProcess($maxNumberOfParallelProc)
    {
        if (null !== $maxNumberOfParallelProc && (int) $maxNumberOfParallelProc > 0) {
            return $maxNumberOfParallelProc;
        }

        $processorCounter = new ProcessorCounter();

        return $processorCounter->execute();
    }

    /**
     * @param  OutputInterface $output
     * @param $queue
     * @param $processManager
     * @return array
     */
    private function doExecute(OutputInterface $output, $queue, $processManager)
    {
        $processes = null;

        if ($this->isVerbose($output)) {
            $progressBar = new VerboseRenderer($queue->count(), $output, $processManager->getNumberOfProcessExecutedByTheBeforeCommand());
        } else {
            $progressBar = new ProgressBarRenderer($queue->count(), $output, $this->getHelper('progress'), $processManager->getNumberOfProcessExecutedByTheBeforeCommand());
        }

        $progressBar->renderHeader($queue, $processes);

        while ($processManager->assertNProcessRunning($queue, $processes)) {
            $progressBar->renderBody($queue, $processes);
        }

        $processes->wait();
        $progressBar->renderFooter($queue, $processes);

        return $processes;
    }

    private function isVerbose($output)
    {
        if (OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity()) {
            return true;
        }

        return false;
    }

    private function executeBeforeCommand($queue, $processes, $output, $processManager)
    {
        if (!$processes->isSuccessful()) {
            $array = $processes->getErrorOutput();
            $output->writeln(sprintf("Re-Running [%d] elements", count($array)));
            $queue->push(new TestsQueue(array_keys($array)));
            $processes = $this->doExecute($output, $queue, $processManager);
        }

        return $processes;
    }
}
