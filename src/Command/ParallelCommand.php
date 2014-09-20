<?php

namespace Liuggio\Fastest\Command;

use Liuggio\Fastest\ExecuteACommandInParallel;
use Liuggio\Fastest\Process\CreateNProcesses;
use Liuggio\Fastest\Queue\Infrastructure\RedisQueueFactory;
use Liuggio\Fastest\ReadFromInputAndPushIntoTheQueue;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class ParallelCommand extends Command
{
    private $service;

    private function initServices($queuePort)
    {
        $this->service = array();

        $queueFactory = new RedisQueueFactory($queuePort);
        $this->service['input'] = new ReadFromInputAndPushIntoTheQueue($queueFactory);

        $processFactory = new CreateNProcesses();
        $this->service['executor'] = new ExecuteACommandInParallel($processFactory);
    }

    protected function configure()
    {
        $this
            ->setName('parallel')
            ->setAliases(array('consumer:parallel'))
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
                'Number of process, default: available CPUs.'
            )
            ->addOption(
                'before',
                'b',
                InputOption::VALUE_REQUIRED,
                'Execute a process before consuming the queue, execute it once per Process, useful for init schema and fixtures.'
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
                'queue-key',
                'k',
                InputOption::VALUE_REQUIRED,
                'Queue key number.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $queuePort = (int) $input->getOption('queue-key');
        if ($queuePort <= 0) {
            $queuePort = rand(100000, 200000);
        }

        $this->initServices($queuePort);

        $queue = $this->service['input']
            ->execute(
                $input->getOption('xml'),
                $input->getOption('preserve-order')
            );
        $queuePort = $queue->getQueuePort();
        $number = $queue->getNumberOfPushedMessage();

        $shuffled = $input->getOption('preserve-order')?'':'shuffled';
        $output->writeln('- <fg=white;bg=blue>'.$number.'</> '.$shuffled.' tests into the queue that has '.$queue->getMessagesInTheQueue().' tests.');
        $output->writeln('- Queue port is at '.$queuePort.'.');

        $processes = $this->service['executor']->execute(
            $queuePort,
            $input->getArgument('execute'),
            $input->getOption('process'),
            $input->getOption('before')
        );
        $output->writeln('- Will be consumed by <fg=white;bg=blue>'.$processes->count().'</> parallel Processes.');

        $output->writeln('');

        $progressBar = new UIProgressBar();
        $progressBar->render($queue, $output, $processes);

        $processes->wait();
        $queue->close();

        $rendererFinalOutput = new RenderFinalOutputInformation();
        $rendererFinalOutput->render($output, $processes);


        $out = "    <info>✔</info> You are great!";
        if (!$processes->isSuccessful()) {
            $out = "    <error>✘ ehm broken tests...</error>";
        }

        $output->writeln(PHP_EOL.$out);

        return $processes->getExitCode();
    }


}
