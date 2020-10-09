<?php

namespace Liuggio\Fastest\Queue;

class CreateTestsQueueFromSTDIN
{
    /**
     * @var string
     */
    private $stdin;

    /**
     * @var resource|false|null
     */
    private $fstin;

    public function __construct(string $stdin = 'php://stdin')
    {
        $this->stdin = $stdin;
        $this->fstin = null;
    }

    public function execute(): TestsQueue
    {
        $fstin = fopen($this->stdin, 'r');
        if (!$fstin) {
            throw new \Exception(sprintf('Cannot read from STDIN: %s', $this->stdin));
        }

        $testSuites = new TestsQueue();
        while (false !== ($line = fgets($fstin))) {
            $this->addLineIfNotEmpty($testSuites, (string) $line);
        }

        $this->fstin = $fstin;

        return $testSuites;
    }

    public function __destruct()
    {
        if ($this->fstin) {
            fclose($this->fstin);
        }
    }

    private function addLineIfNotEmpty(TestsQueue &$testSuites, string $line): void
    {
        $line = trim($line);
        if (!empty($line)) {
            $testSuites->add($line);
        }
    }
}
