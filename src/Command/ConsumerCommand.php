<?php

namespace Liuggio\Fastest\Command;

use Liuggio\Fastest\Process\EnvCommandCreator;
use Liuggio\Fastest\Queue\Consumer;
use Liuggio\Fastest\Queue\Infrastructure\MsqQueueFactory;
use Liuggio\Fastest\Queue\Infrastructure\RedisQueueFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class ConsumerCommand extends Command
{
    const PREFIX_SUITE_ERROR   = '| x Suite Error ';
    const PREFIX_SUITE_SUCCESS = '|   Suite OK    ';

    protected function configure()
    {
        $this
            ->setName('consume')
            ->setAliases(array('consumer:single'))
            ->setDescription('Consume in a single process one element or looping with -l as option.')
            ->addArgument('fifo-file',
                InputArgument::REQUIRED,
                'Path of the FIFO file.'
            )
            ->addArgument('execute',
                InputArgument::OPTIONAL,
                'Command to execute, if omitted will be \'phpunit {}\'',
                'phpunit {}'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output, $recursive = false)
    {
        $factory = new RedisQueueFactory((int) $input->getArgument('fifo-file'));
        $consumer = new Consumer($factory);

        return $this->doExecute($consumer, $input, $output, rand());
    }

    protected function doExecute($consumer, InputInterface $input, OutputInterface $output, $rand)
    {
        $suite = $consumer->execute();
        if (!$suite) {
            return 0;
        }

        $execute = $input->getArgument('execute');
        $commandToExecute = str_replace('{}', $suite, $execute);
        $commandToExecute = str_replace('{p}', getenv(EnvCommandCreator::ENV_TEST_NUMBER), $commandToExecute);

        $ret = $this->executeACommand($suite, $commandToExecute, $output);

        return $this->returnExitCodeAs($ret, $this->doExecute($consumer, $input, $output, $rand));
    }

    private function executeACommand($suite, $command, OutputInterface $output)
    {
        $process = new Process($command);
        $process->setTimeout(null);
        $process->setIdleTimeout(null);

        $process->run();
//        $process->run(function ($type, $buffer) use (){
//                if (Process::ERR === $type) {
//                    echo 'ERR > '.$buffer;
//                } else {
//                    echo 'OUT > '.$buffer;
//                }
//            });

      if (!$process->isSuccessful()) {
            $output->writeln(sprintf("%s [%s] %s",self::PREFIX_SUITE_ERROR, getenv(EnvCommandCreator::ENV_TEST_NUMBER), $suite));
            $output->writeln($process->getOutput());
            $output->writeln($process->getErrorOutput());

        } else {
          $output->writeln(sprintf("%s [%s] %s",self::PREFIX_SUITE_SUCCESS, getenv(EnvCommandCreator::ENV_TEST_NUMBER), $suite));
      }

        return $process->getExitCode();
    }

    private function returnExitCodeAs($past, $current)
    {
        if ((int) $past !=0 || (int) $current != 0) {
            return max((int) $past, (int) $current);
        }

        return 0;
    }
}
