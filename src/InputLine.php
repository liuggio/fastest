<?php

namespace Liuggio\Fastest;

/**
 * Is the input string, with template it will be a valid command to be executed.
 */
class InputLine
{
    /**
     * @var string
     */
    private $inputValue;

    /**
     * Command constructor.
     *
     * @param string $commandValue
     */
    public function __construct($commandValue)
    {
        $this->inputValue = (string) $commandValue;
    }

    /**
     * Creates an empty InputLine.
     *
     * @return InputLine
     */
    public static function createEmpty()
    {
        return new self('');
    }

    /**
     * Creates a new CommandLine given a line string.
     *
     * @param string $string
     *
     * @return static
     */
    public static function fromString($string)
    {
        return new self($string);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->inputValue;
    }
}
