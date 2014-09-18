<?php

namespace Liuggio\Fastest;

use Liuggio\Fastest\Process\CreateNProcesses;
use Liuggio\Fastest\Process\Processes;
use Liuggio\Fastest\Process\PrepareCommandToExecute;

class ExecuteACommandInParallel
{
    private $processFactory;
    private $commandCreator;

    public function __construct(CreateNProcesses $processFactory = null, PrepareCommandToExecute $commandCreator = null)
    {
        $this->processFactory = $processFactory;
        $this->commandCreator = $commandCreator;

        if (null === $this->processFactory) {
            $this->processFactory = new CreateNProcesses();
        }
        if (null === $this->commandCreator) {
            $this->commandCreator = new PrepareCommandToExecute();
        }
    }

    /**
     * @todo if there's no functional fast as hell!
     */
    public function execute($queuePort, $cmdToExecute = null, $forceToNProcesses = null, $beforeCommand = null)
    {
        if (null !== $beforeCommand) {
            $processes = $this->processFactory->execute($beforeCommand, $forceToNProcesses);
            $this->start($processes);
            $this->wait($processes);
        }

        $cmdToExecute = $this->commandCreator->execute($queuePort, $cmdToExecute);

        $processes = $this->processFactory->execute($cmdToExecute, $forceToNProcesses);
        $this->start($processes);

        return $processes;
    }

    private function start(Processes $processes)
    {
        $processes->start();
    }

    private function wait(Processes $processes)
    {
        $processes->wait();
    }
}
