<?php

namespace Liuggio\Fastest\Command;

use Liuggio\Fastest\Queue\PopATestSuite;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Stopwatch\Stopwatch;

class ConsumerCommand extends Command
{
    private $popATestSuite;

    function __construct(PopATestSuite $popATestSuite)
    {
        $this->popATestSuite = $popATestSuite;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('consume')
            ->setAliases(array('consumer:single'))
            ->setDescription('Consume in a single process one element or looping with -l as option.')
            ->addArgument('execute',
                InputArgument::OPTIONAL,
                'Command to execute, if omitted will be \'phpunit {}\''
            )
            ->addOption('loop',
                'l',
                InputOption::VALUE_NONE,
                'Loop consuming the Queue.'
            )
            ->addOption('xml',
                'x',
                InputOption::VALUE_REQUIRED,
                'Read input from a phpunit xml file from the \'<testsuites>\' collection.'
            )
        ;
    }


    private function executeCommand($suite, $command, OutputInterface $output)
    {
        $prefix = '['.(int)getenv('TEST_ENV_NUMBER')."]\t";
        $stopwatch = new Stopwatch();
        $stopwatch->start($command);

        $process = new Process($command);
        $process->setTimeout(null);
        $process->setIdleTimeout(null);

        $this->getApplication()->getService('log')->addInfo($prefix.$suite."\t".$command);
        $process->run();

        $out = "<info>✔</info>";
        if (!$process->isSuccessful()) {
            $out = "<error>✘</error>";

            $this->getApplication()->getService('log')->addError('┏ '.$prefix.$suite."\t".$process->getOutput());
            $this->getApplication()->getService('log')->addError('┃ '.$prefix.$suite."\t".$process->getErrorOutput()."\trunning: ".$command);
            $this->getApplication()->getService('log')->addError('┗ '.$prefix.$suite."\t");
        }

        $this->getApplication()->getService('log')->addInfo($process->getOutput());

        $event = $stopwatch->stop($command);

        $out .= "\t";
        $out .= $event->getDuration()."ms\t";
        $out .= $event->getMemory()."B\t";
        $this->getApplication()->getService('log')->addInfo($prefix.$suite."\t".$out);
        $out .= $suite;
        $output->writeln($prefix.$out);

        return $process->getExitCode();
    }

    protected function execute(InputInterface $input, OutputInterface $output, $recursive = false)
    {
        $suite = $this->popATestSuite->execute();

        if (!$suite){
            $out = '['.(int)getenv('TEST_ENV_NUMBER')."]\t-\tNothing to do empty queue.";

            if (!$recursive) {
                $this->getApplication()->getService('log')->addWarning($out);
                $output->writeln($out);
                return 0;
            }

            $this->getApplication()->getService('log')->addInfo($out);
            return 0;
        }


        $execute = $input->getArgument('execute');
        if (!$execute) {
            $execute = 'phpunit {}';
        }

        $commandToExecute = str_replace('{}', $suite, $execute);

        if (OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity()) {
            $output->writeln($suite.', executing['.$commandToExecute.']');
        }

        $return = $this->executeCommand($suite, $commandToExecute, $output);

        if ($input->getOption('loop')) {

            return $this->returnExitCodeAs($return, $this->execute($input, $output, true));
        }

        return $output;
    }

    private function returnExitCodeAs($past, $current)
    {
        if ((int)$past !=0 || (int)$current != 0) {

            return max((int)$past, (int)$current);
        }

        return 0;
    }
}
