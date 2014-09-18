<?php

namespace Liuggio\Fastest\Command;

use Liuggio\Fastest\ExecuteACommandInParallel;
use Liuggio\Fastest\Process\CreateNProcesses;
use Liuggio\Fastest\Queue\Infrastructure\MsqQueueFactory;
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

        $queueFactory = new MsqQueueFactory($queuePort);
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
        $output->writeln('- Queue has <fg=white;bg=blue>'.$number.'</> '.$shuffled.' elements.');
        // $output->writeln('- Queue port is at '.$queuePort.'.');

        $processes = $this->service['executor']->execute(
            $queuePort,
            $input->getArgument('execute'),
            $input->getOption('process'),
            $input->getOption('before')
        );
        $output->writeln('- Will be consumed by <fg=white;bg=blue>'.$processes->count().'</> parallel Processes.');
        ProgressBar::setFormatDefinition(
            'minimal',
            '<info>%percent%</info>\033[32m%\033[0m <fg=white;bg=blue>%remaining%</>'
        );

        $output->writeln('');
        $progress = new ProgressBar($output, $number);
        $progress->setFormat(' %current%/%max% <fg=white;bg=blue>[%bar%]</> %percent:3s%% %elapsed:6s% %memory:6s%');
        $progress->start();
        $last = $number;
        $now = -1;
        while (($now = $queue->getMessagesInTheQueue()) > 0) {

            if ($last != $now) {
                $progress->advance($last-$now);
            }
            $last = $now;
            usleep(10);
        }
        $progress->finish();
        $processes->wait();
        $queue->close();
        $value = 0;
        $output->writeln('');

        $array = $processes->getProcesses();

        foreach ($array as $process) {
            $exit = $process->getExitCode();
            if (0 !== $exit) {
                $output->writeln('['.$process->getOutput().']');
                $output->writeln('['.$process->getErrorOutput().']');
            }
            $value = $this->returnExitCodeAs($exit, $value);
        }

        $out = "    <info>✔</info> You are great!";
        if (0 !== $value) {
            $out = "    <error>✘ ehm broken tests...</error>";
        }

        $output->writeln(PHP_EOL.$out);

        return $value;
    }

    private function returnExitCodeAs($past, $current)
    {
        if ((int) $past !=0 || (int) $current != 0) {
            return max((int) $past, (int) $current);
        }

        return 0;
    }

}
