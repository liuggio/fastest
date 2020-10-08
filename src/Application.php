<?php

namespace Liuggio\Fastest;

use Liuggio\Fastest\Command\ParallelCommand;
use Symfony\Component\Console\Application as BaseApp;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;

class Application extends BaseApp
{
    const VERSION = '1.4';

    public function __construct()
    {
        parent::__construct('fastest', self::VERSION);
    }

    protected function getCommandName(InputInterface $input): string
    {
        // This should return the name of your command.
        return 'fastest';
    }

    /**
     * @return array<ParallelCommand|Command>
     */
    protected function getDefaultCommands(): array
    {
        // Keep the core default commands to have the HelpCommand
        // which is used when using the --help option
        $defaultCommands = parent::getDefaultCommands();

        $defaultCommands[] = new ParallelCommand();

        return $defaultCommands;
    }

    /**
     * Overridden so that the application doesn't expect the command
     * name to be the first argument.
     */
    public function getDefinition(): InputDefinition
    {
        $inputDefinition = parent::getDefinition();
        // clear out the normal first argument, which is the command name
        $inputDefinition->setArguments();

        return $inputDefinition;
    }
}
