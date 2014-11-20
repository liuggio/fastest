<?php

namespace Liuggio\Fastest\Queue;

class CreateTestsQueueFromSTDIN
{
    private $stdin;
    private $fstin;

    public function __construct($stdin = 'php://stdin')
    {
        $this->stdin = $stdin;
        $this->fstin = null;
    }

    public function execute()
    {
        $this->fstin = fopen($this->stdin, 'r');
        $testSuites = new TestsQueue();
        while (false !== ($line = fgets($this->fstin))) {
            $this->addLineIfNotEmpty($testSuites, $line);
        }

        return $testSuites;
    }

    public function __destruct()
    {
        if (null !== $this->fstin) {
            fclose($this->fstin);
        }
    }

    private function addLineIfNotEmpty(TestsQueue &$testSuites, $line)
    {
        $line = trim($line);
        if (!empty($line)) {
            $testSuites->add($line);
        }
    }
}
