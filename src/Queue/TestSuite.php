<?php

namespace Liuggio\Fastest\Queue;

class TestSuite
{
    /**
     * @var string
     */
    protected $testPath;

    public function __construct(string $testPath)
    {
        $checkTestPath = trim($testPath);
        if (empty($checkTestPath)) {
            throw new \Exception('empty value');
        }

        $this->testPath = $checkTestPath;
    }

    public function __toString(): string
    {
        return $this->testPath;
    }

    public function getTestPath(): string
    {
        return $this->testPath;
    }
}
