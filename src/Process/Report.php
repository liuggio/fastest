<?php

namespace Liuggio\Fastest\Process;

class Report
{
    private $suite;
    private $isSuccess;
    private $processorNumber;
    private $time;
    private $errorBuffer;
    private $isFirstOnThread;

    /**
     * @param string      $suite
     * @param bool        $isSuccess
     * @param int         $time
     * @param int         $processorNumber
     * @param string|null $errorBuffer
     * @param bool        $isFirstOnThread
     */
    public function __construct(
        $suite,
        $isSuccess,
        $time,
        $processorNumber,
        $errorBuffer,
        $isFirstOnThread
    ) {
        $this->isSuccess = $isSuccess;
        $this->processorNumber = $processorNumber;
        $this->suite = $suite;
        $this->errorBuffer = $errorBuffer;
        $this->isFirstOnThread = $isFirstOnThread;
        $this->time = $time;
    }

    /**
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->isSuccess;
    }

    /**
     * @return int
     */
    public function getProcessorNumber()
    {
        return $this->processorNumber;
    }

    /**
     * @return bool
     */
    public function isFirstOnThread()
    {
        return $this->isFirstOnThread;
    }

    /**
     * @return string
     */
    public function getSuite()
    {
        return $this->suite;
    }

    /**
     * @return int Time in microseconds
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @return string|null
     */
    public function getErrorBuffer()
    {
        return $this->errorBuffer;
    }
}
