<?php

namespace Liuggio\Fastest\Queue;

class PushTestSuites
{
    protected $queue;

    function __construct(PushQueueInterface $queue)
    {
        $this->queue = $queue;
    }

    /**
     * @param TestSuite[] $testSuites
     *
     * @return int the number of testSuite pushed
     */
    public function execute($testSuites)
    {
        if (!is_array($testSuites)) {
            $testSuites = array($testSuites);
        }

        $counter = 0;
        foreach ($testSuites as $testSuite)
        {
            if (!($testSuite instanceof TestSuite)) {
                $testSuite = new TestSuite($testSuite);
            }

            if ($this->queue->push($testSuite)) {
                $counter++;
            }
        }

        return $counter;
    }
} 