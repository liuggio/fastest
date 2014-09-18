<?php

namespace Liuggio\Fastest\Queue;

class TestSuite
{
    protected $testPath;

    public function __construct($testPath)
    {
        $checkTestPath = trim((string) $testPath);
        if (null === $testPath || empty($checkTestPath)) {
            throw new \Exception('empty value');
        }

        $this->testPath = $checkTestPath;
    }

    public function __toString()
    {
        return (string) $this->testPath;
    }

    public function getTestPath()
    {
        return $this->testPath;
    }
}
