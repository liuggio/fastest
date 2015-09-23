<?php

namespace Liuggio\Fastest\Process;

use Liuggio\Fastest\CommandLine;

class ApplyCommandLineTemplate
{
    /** @var string */
    private $commandLineTemplate;

    /**
     * Create a inputLine using the default the template.
     *
     * @param CommandLine        $commandLineTemplate
     * @param ProcessEnvironment $processEnvironment
     *
     * @return CommandLine
     */
    public function createCommandLine(CommandLine $commandLineTemplate, ProcessEnvironment $processEnvironment)
    {
        return $this->fillCorrectArgumentsOnCommandLine($commandLineTemplate, $processEnvironment);
    }

    private function fillCorrectArgumentsOnCommandLine(CommandLine $template, ProcessEnvironment $processEnvironment)
    {
        $commandToExecute = str_replace('{}', (string) $processEnvironment->getInputLine(), (string) $template);
        $commandToExecute = str_replace('{p}', $processEnvironment->getChannelId(), $commandToExecute);
        $commandToExecute = str_replace('{inc}', $processEnvironment->getIncrementalNumber(), $commandToExecute);

        return new CommandLine($commandToExecute);
    }
}
