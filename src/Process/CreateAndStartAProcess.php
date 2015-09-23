<?php

namespace Liuggio\Fastest\Process;

use Liuggio\Fastest\Channel;
use Liuggio\Fastest\CommandLine;
use Liuggio\Fastest\InputLine;

class CreateAndStartAProcess
{
    /**
     * Creates a process and waits the end, dispatching events.
     *
     * @param Channel     $channel
     * @param CommandLine $baseCommandLine
     * @param InputLine   $inputLine
     * @param int         $currentProcessCounter
     *
     * @return Process
     */
    public function createAndStartAProcess(Channel $channel, CommandLine $baseCommandLine, InputLine $inputLine, $currentProcessCounter)
    {
        $process = $this->create($channel, $baseCommandLine, $inputLine, $currentProcessCounter);
        $process->start();

        return $process;
    }

    private function create(Channel $channel, CommandLine $baseCommandLine, InputLine $inputLine, $incrementalProcessCounter)
    {
        $envs = new ProcessEnvironment($channel, $inputLine, $incrementalProcessCounter);
        $template = new ApplyCommandLineTemplate();
        $commandLineToExecute = $template->createCommandLine($baseCommandLine, $envs);
        $process = new Process($commandLineToExecute, $envs);

        return $process;
    }
}
