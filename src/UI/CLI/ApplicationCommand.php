<?php

namespace Liuggio\Fastest\UI\CLI;

use Liuggio\Fastest\CommandLine;
use Liuggio\Fastest\Consumer\ConsumerListener;
use Liuggio\Fastest\Event\EventsName;
use Liuggio\Fastest\ProcessorCounter;
use Liuggio\Fastest\Producer\StdInProducer;
use Liuggio\Fastest\Queue\EventDispatcherQueue;
use Liuggio\Fastest\Supervisor;
use Liuggio\Fastest\UI\StdOutUISubscriber;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Stopwatch\Stopwatch;

class ApplicationCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('fastest')
            ->setDescription('Consume the elements in parallel.')
            ->addArgument(
                'execute',
                InputArgument::OPTIONAL,
                'Optional command to create.'
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
            ->addOption(
                'no-errors-summary',
                null,
                InputOption::VALUE_NONE,
                'Do not display all errors after the test run. Useful with --vv because it already displays errors immediately after they happen.'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $stopWatch = new Stopwatch();
        $stopWatch->start('create');
        // init
        $inputProducer = new StdInProducer();
        $eventDispatcher = new EventDispatcher();

        // arguments and options
        $maxNumberOfParallelProc = $this->getMaxNumberOfProcess($input->getOption('process'));
        $randomize = $input->getOption('preserve-order') ? true : false;
        $executeCommandLine = $this->getExecuteCommandLine($input);

        $queue = new EventDispatcherQueue($eventDispatcher);

        $consumerListener = new ConsumerListener($queue, $executeCommandLine, $eventDispatcher);
        $supervisor = new Supervisor($eventDispatcher, $maxNumberOfParallelProc);

        $this->addListenersAndSubscribers($eventDispatcher, $supervisor, $consumerListener);

        if ($randomize) {
            $queue = $queue->randomize();
        }

        // start
        $inputProducer->produce($queue);

        return $supervisor->loop();
    }

    /**
     * @param int $maxNumberOfParallelProc
     *
     * @return int
     */
    private function getMaxNumberOfProcess($maxNumberOfParallelProc)
    {
        if (null !== $maxNumberOfParallelProc && (int) $maxNumberOfParallelProc > 0) {
            return $maxNumberOfParallelProc;
        }

        $processorCounter = new ProcessorCounter();

        return $processorCounter->execute();
    }

    private function addListenersAndSubscribers($eventDispatcher, $supervisor, $consumerListener)
    {
    /**
     * @param EventDispatcher          $eventDispatcher
     * @param EventSubscriberInterface $supervisor
     * @param mixed                    $consumerListener
     */
        // init dispatcher
        $eventDispatcher->addSubscriber(new StdOutUISubscriber());
        $eventDispatcher->addSubscriber($supervisor);
        $eventDispatcher->addListener(
            EventsName::CHANNEL_IS_WAITING,
            array($consumerListener, 'onChannelIsWaiting')
        );
    }

    /**
     * @param InputInterface $input
     *
     * @return CommandLine
     */
    protected function getExecuteCommandLine(InputInterface $input)
    {
        if (!$input->getArgument('execute')) {
            return CommandLine::createDefault();
        }

        return CommandLine::fromString($input->getArgument('execute'));
    }
}
