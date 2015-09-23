<?php

namespace Liuggio\Fastest;

/**
 * Value Object for the Command to be executed in parallel.
 */
class CommandLine
{
    const DEFAULT_COMMAND_TO_EXECUTE_TPL = 'bin/phpunit {}';

    /** @var string */
    private $commandValue;

    /**
     * Command constructor.
     *
     * @param string $commandValue
     */
    public function __construct($commandValue)
    {
        $this->commandValue = (string) $commandValue;
    }

    /**
     * Creates a new CommandLine given a line string.
     *
     * @param $string
     *
     * @return static
     */
    public static function fromString($string)
    {
        return new self($string);
    }

    /**
     * @return CommandLine
     */
    public static function createDefault()
    {
        return new self(self::DEFAULT_COMMAND_TO_EXECUTE_TPL);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->commandValue;
    }
}
