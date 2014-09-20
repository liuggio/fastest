<?php

namespace Liuggio\Fastest\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

use Liuggio\Fastest\Process\Processes;
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

        $maxNumberOfParallelProc = $this->getMaxNumberOfProcess($input->getOption('preserve-order'));
        $processFactory = new ProcessFactory($maxNumberOfParallelProc, $input->getArgument('execute'));
        $processManager =  new ProcessesManager($processFactory, $maxNumberOfParallelProc, $input->getOption('before'));

        // header
        $shuffled = $input->getOption('preserve-order')?'':'shuffled ';
        $output->writeln('- <fg=white;bg=blue>'.$queue->count().'</> '.$shuffled.'tests into the queue.');

        $output->writeln('- Will be consumed by <fg=white;bg=blue>'.$maxNumberOfParallelProc.'</> parallel Processes.');

        // loop
        $processes = null;

        if ($output->isVerbose()) {
            $progressBar = new UIVerboseProgressBar($queue->count(), $output);
        } else {
            $progressBar = new UIProgressBar($queue->count(), $output);
        }

        while ($processManager->assertNProcessRunning($queue, $processes)) {
            $progressBar->render($queue, $processes);
        }

        $processes->wait();
        $progressBar->finish($queue, $processes);
        $output->writeln('');
        // render footer
        $rendererFinalOutput = new RenderFinalOutputInformation();
        $rendererFinalOutput->render($output, $processes);

        $out = "    <info>✔</info> You are great!";
        if (!$processes->isSuccessful()) {
            $out = "    <error>✘ ehm broken tests...</error>";
        }

        $event =$stopWatch->stop('execute');


        $output->writeln(PHP_EOL.$out);
        $output->writeln( sprintf("    Time: %d ms, Memory: %d b", $event->getDuration(), $event->getMemory()));

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

}
