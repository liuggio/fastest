<?php

namespace Liuggio\Fastest\Command;


use Liuggio\Fastest\Process\Processes;
use Liuggio\Fastest\Queue\QueueInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class UIProgressBar
{

    public function render(QueueInterface $queue, OutputInterface $output, Processes $processes)
    {
        $messageInTheQueue = $queue->getMessagesInTheQueue();
        $progress = new ProgressBar($output, $messageInTheQueue);
        $progress->setFormat('very_verbose');
        $progress->setFormat('%current%/%max% <fg=white;bg=blue>[%bar%]</> %percent:3s%% %elapsed:6s% %memory:6s%'.PHP_EOL.PHP_EOL.'    %message%.'.PHP_EOL.PHP_EOL);

        $progress->start();
        $last = $messageInTheQueue;
        $now = -1;
        $progress->setMessage('<info>0</info> failures');
        $failure = 0;

        while ($processes->isAnyStillRunning()) {

            $now = $queue->getMessagesInTheQueue();
            $errorCount = $processes->countIncrementalErrors();

            if ($errorCount > 0) {
                $failure += $errorCount;
                $progress->setMessage(sprintf("<error>%d</error> failures.", $failure));
                $progress->setFormat('%current%/%max% <fg=white;bg=blue>[%bar%]</> %percent:3s%% %elapsed:6s% %memory:6s%'.PHP_EOL.PHP_EOL.'     %message%.'.PHP_EOL.PHP_EOL);
            }

            if ($last != $now) {
                $progress->advance($last-$now);
            }

            $last = $now;
            usleep(600);
        }
        $progress->finish();

        return $failure;
    }
} 