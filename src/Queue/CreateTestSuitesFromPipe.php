<?php

namespace Liuggio\Fastest\Queue;

class CreateTestSuitesFromPipe
{
    private $stdin;
    private $fstin;

    function __construct($stdin = 'php://stdin')
    {
        $this->stdin = $stdin;
        $this->fstin = null;
    }

    public function execute()
    {
        $this->fstin = fopen($this->stdin, 'r');
        $arrayOfInput = array();
        stream_set_blocking($this->fstin , false);
        while (false !== ($line = fgets($this->fstin ))) {
            $this->addLineIfNotEmpty($arrayOfInput, $line);
        }

        return $arrayOfInput;
    }

    public function __destruct()
    {
        if (null !== $this->fstin) {
            fclose($this->fstin);
        }
    }

    private function addLineIfNotEmpty(&$arrayOfInput, $line)
    {
        $line = trim($line);
        if (!empty($line)) {
            $arrayOfInput[] = new TestSuite($line);
        }
    }
} 