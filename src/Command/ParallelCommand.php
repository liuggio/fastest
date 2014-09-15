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

class ParallelCommand extends Command
{
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
                'Number of process, default: available CPUs'
            )
            ->addOption(
                'log-dir',
                null,
                InputOption::VALUE_REQUIRED,
                'log-dir \''.sys_get_temp_dir().'\' by default'
            )
            ->addOption(
                'only-push',
                'i',
                InputOption::VALUE_NONE,
                'Only from input, push the data into the queue.'
            )
            ->addOption(
                'xml',
                'x',
                InputOption::VALUE_REQUIRED,
                'Read input from a phpunit xml file from the \'<testsuites>\' collection, it is not used for consuming.'
            )
            ->addOption(
                'stop-on-error',
                'e',
                InputOption::VALUE_NONE,
                'Stop tests on error.'
            )
        ;
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // get Input and push Input
        $readFromPipe = $this->getAndPushInputFromPipe();
        if ($xmlFile = $input->getOption('xml')) {
            if ($readFromPipe) {
                throw new \InvalidArgumentException('Not use the xml as input if you used with pipe.');
            }
            $readFromPipe = $this->getAndPushInputFromXML($xmlFile);
        }

        if (($readFromPipe>0)) {
            $output->writeln('Pushed n.'.$readFromPipe.' element in the Queue');
        }

        if ($input->getOption('only-push')) {
            if (($readFromPipe<=0)) {
                $output->writeln('Nothing to do.');
            }
            return 0;
        }

        $cmd = $this->getApplication()->getService('parallel_command')
            ->execute(
                $input->getArgument('execute'),
                $input->getOption('process'),
                $input->getOption('log-dir')
            );

        putenv('TEST_ENV_ENABLE=1');
        if (OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity()) {
            $output->writeln('Running in parallel: '.$cmd.'.');
        }

        return $this->executeCommand($cmd, $output);
    }

    private function getAndPushInputFromXML($xmlFileName)
    {
        $inputGot = $this->getApplication()->getService('xml')->execute($xmlFileName);

        if (is_array($inputGot) && count($inputGot) > 0) {
            $this->getApplication()->getService('push')->execute($inputGot);

            return count($inputGot);
        }

        return false;
    }

    private function getAndPushInputFromPipe()
    {
        $inputGot = $this->getApplication()->getService('pipe')->execute();

        if (is_array($inputGot) && count($inputGot) > 0) {
            $this->getApplication()->getService('push')->execute($inputGot);

            return count($inputGot);
        }

        return false;
    }

    private function executeCommand($command,OutputInterface $output)
    {
        $out = PHP_EOL."   \t";
        $stopwatch = new Stopwatch();
        $stopwatch->start($command);

        $process = new Process($command);
        $process->setTimeout(null);
        $process->setIdleTimeout(null);
        $process->run(function ($type, $buffer) use ($output) {
            if (Process::ERR === $type) {
                $output->write('<error>'.$buffer.'</error>');
            } else {
                $output->write($buffer);
            }
        });

        $endTag = '</info>';
        if (!$process->isSuccessful()) {
            $out .= "<error>✘\t";
            $endTag = '</error>';
        } else {
            $out .= "<info>✔\t";
        }

        $event = $stopwatch->stop($command);
        $out .= $event->getDuration()."ms\t";
        $out .= $event->getMemory()."B\t";
        $out .= $endTag;
        $output->writeln($out);

        return $process->getExitCode();
    }
}
