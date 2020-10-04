<?php

namespace Liuggio\Fastest\Process;

class Report
{
    /**
     * @var string
     */
    private $suite;

    /**
     * @var bool
     */
    private $isSuccess;

    /**
     * @var int
     */
    private $processorNumber;

    /**
     * @var float
     */
    private $time;

    /**
     * @var string|null
     */
    private $errorBuffer;

    /**
     * @var bool
     */
    private $isFirstOnThread;

    public function __construct(
        string $suite,
        bool $isSuccess,
        float $time,
        int $processorNumber,
        ?string $errorBuffer,
        bool $isFirstOnThread
    ) {
        $this->isSuccess = $isSuccess;
        $this->processorNumber = $processorNumber;
        $this->suite = $suite;
        $this->errorBuffer = $errorBuffer;
        $this->isFirstOnThread = $isFirstOnThread;
        $this->time = $time;
    }

    public function isSuccessful(): bool
    {
        return $this->isSuccess;
    }

    public function getProcessorNumber(): int
    {
        return $this->processorNumber;
    }

    public function isFirstOnThread(): bool
    {
        return $this->isFirstOnThread;
    }

    public function getSuite(): string
    {
        return $this->suite;
    }

    public function getTime(): float
    {
        return $this->time;
    }

    public function getErrorBuffer(): ?string
    {
        return $this->errorBuffer;
    }
}
