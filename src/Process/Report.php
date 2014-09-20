<?php

namespace Liuggio\Fastest\Process;


class Report
{
    private $suite;
    private $isSuccess;
    private $processorNumber;
    private $time;

    function __construct($suite, $isSuccess, $processorNumber, $errorBuffer)
    {
        $this->isSuccess = $isSuccess;
        $this->processorNumber = $processorNumber;
        $this->suite = $suite;
        $this->errorBuffer = $errorBuffer;
    }

    /**
     * @return mixed
     */
    public function isSuccessful()
    {
        return $this->isSuccess;
    }

    /**
     * @return mixed
     */
    public function getProcessorNumber()
    {
        return $this->processorNumber;
    }

    /**
     * @return mixed
     */
    public function getSuite()
    {
        return $this->suite;
    }

    /**
     * @return mixed
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @return mixed
     */
    public function getErrorBuffer()
    {
        return $this->errorBuffer;
    }


} 