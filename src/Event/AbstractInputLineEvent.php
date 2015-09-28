<?php

namespace Liuggio\Fastest\Event;

use Liuggio\Fastest\InputLine;
use Symfony\Component\EventDispatcher\Event;

abstract class AbstractInputLineEvent extends Event
{
    /**
     * @var InputLine
     */
    private $inputLine;

    /**
     * @param InputLine $inputLine
     */
    public function __construct(InputLine $command)
    {
        $this->inputLine = $command;
    }

    /**
     * @return InputLine
     */
    public function getInputLine()
    {
        return $this->inputLine;
    }
}
