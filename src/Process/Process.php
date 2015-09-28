<?php

namespace Liuggio\Fastest\Process;

use Liuggio\Fastest\CommandLine;
use Liuggio\Fastest\InputLine;
use Symfony\Component\Process\Process as BaseProcess;

class Process extends BaseProcess
{
    /**
     * @var ProcessEnvironment
     */
    private $processEnvironment;

    /**
     * @param CommandLine        $commandLine
     * @param ProcessEnvironment $processEnvironment
     * @param int|float|null     $timeout
     */
    public function __construct(
        CommandLine $commandLine,
        ProcessEnvironment $processEnvironment,
        $timeout = null
    ) {
        $this->processEnvironment = $processEnvironment;

        parent::__construct((string) $commandLine, null, $this->processEnvironment->exportToEnvsArray());
        $this->setTimeout($timeout);
        // compatibility to SF 2.2
        if (method_exists($this, 'setIdleTimeout')) {
            $this->setIdleTimeout($timeout);
        }
    }

    /**
     * @return InputLine
     */
    public function getInputLine()
    {
        return new InputLine($this->processEnvironment->getArgument());
    }

    /**
     * @return CommandLine
     */
    public function getCommandLine()
    {
        return new CommandLine(parent::getCommandLine());
    }

    /**
     * @return int
     */
    public function getIncrementalNumber()
    {
        return $this->processEnvironment->getIncrementalNumber();
    }

    /**
     * @return \Liuggio\Fastest\Channel
     */
    public function getChannel()
    {
        return $this->processEnvironment->getChannel();
    }
}
