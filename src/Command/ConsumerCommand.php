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


    private function executeCommand($suite, $command)
    {
        $prefix = '['.(int)getenv('TEST_ENV_NUMBER')."]\t";
        $stopwatch = new Stopwatch();
        $stopwatch->start($command);

        $process = new Process($command);

        $this->getApplication()->getService('log')->addInfo($prefix.$suite."\t".$command);
        $process->run();

        $out = "<info>✔";
        $endTag = '</info>';
        if (!$process->isSuccessful()) {
            $out = "<error>✘";
            $endTag = '</error>';

            $this->getApplication()->getService('log')->addError($prefix.$suite."\t".$process->getErrorOutput()."\trunning: ".$command);
        }

        $this->getApplication()->getService('log')->addInfo($process->getOutput());

        $event = $stopwatch->stop($command);

        $out .= "\t";
        $out .= $event->getDuration()."ms\t";
        $out .= $event->getMemory()."B\t";
        $this->getApplication()->getService('log')->addInfo($prefix.$suite."\t".$out);
        $out .= $suite. PHP_EOL;

        return $prefix.$out.$endTag;
    }

    protected function execute(InputInterface $input, OutputInterface $output, $recursive = false)
    {
        $suite = $this->popATestSuite->execute();

        if (!$suite){
            $out = '['.(int)getenv('TEST_ENV_NUMBER')."]\t Empty queue.";

            if (!$recursive) {
                $this->getApplication()->getService('log')->addWarning($out);
                $output->writeln($out);
                return 1;
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

        $out = $this->executeCommand($suite, $commandToExecute);

        $output->write($out);

        if ($input->getOption('loop')) {
            return $this->execute($input, $output, true);
        }
    }
}
