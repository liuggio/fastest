<?php

namespace Liuggio\Fastest\Queue;

class TestSuite
{
    protected $testPath;

    function __construct($testPath)
    {
        $this->testPath =(string) $testPath;
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