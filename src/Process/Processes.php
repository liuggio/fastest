<?php

namespace Liuggio\Fastest\Process;

use Symfony\Component\Process\Process;

class Processes
{
    /**
     * @var array<int, Process|null>
     */
    private $processes;

    /**
     * @var array<int, float|null> Unix timestamp with float part of processes start times
     */
    private $startTimes;

    /**
     * @var Report[]
     */
    private $totalBuffer;

    /**
     * @var array<string, string>
     */
    private $errorBuffer;

    /**
     * @var int
     */
    private $errorCounter;

    /**
     * @param Process[] $processes
     */
    public function __construct(array $processes)
    {
        $this->processes = $processes;
        $this->totalBuffer = [];
        $this->errorBuffer = [];
        $this->startTimes = [];
        $this->errorCounter = 0;
    }

    public function cleanUP(bool $addToCompletedQueue = true): void
    {
        foreach ($this->processes as $key => $process) {
            if (null !== $process && $process->isTerminated()) {
                if ($addToCompletedQueue) {
                    $this->moveToCompletedProcesses($key, $process);
                }
                $this->processes[$key] = null;
                $this->startTimes[$key] = null;
            }
        }
    }

    /**
     * @return int[]
     */
    public function getIndexesOfCompletedChannel(): array
    {
        $indexes = [];
        foreach ($this->processes as $index => $process) {
            if (null === $process || $process->isTerminated()) {
                $indexes[] = $index;
            }
        }

        return $indexes;
    }

    /**
     * @param int $key
     * @param Process $process
     */
    public function add(int $key, Process $process): void
    {
        $this->cleanUP();
        if (isset($this->processes[$key]) && null !== $completedProcess = $this->processes[$key]) {
            $this->assertTerminated($key);
            $this->moveToCompletedProcesses($key, $completedProcess);
            $this->processes[$key] = null;
        }

        $this->processes[$key] = $process;
    }

    public function start(int $key = null): bool
    {
        if (null !== $key) {
            $this->startProcess($key);

            return true;
        }

        foreach ($this->processes as $key => $process) {
            if (null !== $process) {
                $this->startProcess($key);
            }
        }

        return true;
    }

    private function startProcess(int $key): void
    {
        if (!isset($this->processes[$key])) {
            return;
        }

        $this->startTimes[$key] = microtime(true);
        $this->processes[$key]->start();
    }

    public function stop(): bool
    {
        return array_walk($this->processes, function (Process $item = null) {
            if (null !== $item) {
                $item->stop();
            }
        });
    }

    /**
     * @param callable|null $terminationCallback A callback to be called after one of the processes is terminated
     * @param bool     $addToCompletedQueue A flag that indicates if this process needs to be added to completedQueue
     *
     * @return bool
     */
    public function wait($terminationCallback = null, $addToCompletedQueue = true): bool
    {
        $lastProcessesRunningCount = $currentRunningProcessesCount = $this->countRunning();
        while ($currentRunningProcessesCount > 0) {
            $currentRunningProcessesCount = $this->countRunning();
            if ($lastProcessesRunningCount !== $currentRunningProcessesCount) {
                $lastProcessesRunningCount = $currentRunningProcessesCount;
                $this->cleanUP($addToCompletedQueue);
                if (null !== $terminationCallback) {
                    call_user_func($terminationCallback);
                }
            }
            usleep(1000);
        }

        return true;
    }

    public function count(): int
    {
        return count($this->processes);
    }

    /**
     * @param int $index
     *
     * @return Process|null
     */
    public function get(int $index): ?Process
    {
        return $this->processes[$index];
    }

    /**
     * @return int Number of processes still running
     */
    public function countRunning(): int
    {
        $count = 0;

        foreach ($this->processes as $process) {
            if (null !== $process && !$process->isTerminated()) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * Return the number of tests failing, 0 if none :).
     *
     * @return int
     */
    public function getExitCode(): int
    {
        return $this->errorCounter;
    }

    /**
     * Checks if the process ended successfully.
     *
     * @return bool true if the process ended successfully, false otherwise
     *
     * @api
     */
    public function isSuccessful(): bool
    {
        return 0 === $this->getExitCode();
    }

    public function countErrors(): int
    {
        return $this->errorCounter;
    }

    /**
     * @return array<string, string>
     */
    public function getErrorOutput(): array
    {
        return $this->errorBuffer;
    }

    /**
     * @param int $key
     *
     * @throws \LogicException
     */
    private function assertTerminated(int $key): void
    {
        if (!isset($this->processes[$key])) {
            return;
        }

        if (!$this->processes[$key]->isTerminated()) {
            throw new \LogicException('Process must be terminated before calling');
        }
    }

    private function moveToCompletedProcesses(int $key, Process $process): void
    {
        $env = $process->getEnv();
        $suite = (string) $env[EnvCommandCreator::ENV_TEST_ARGUMENT];
        $number = $env[EnvCommandCreator::ENV_TEST_CHANNEL];
        $numberOnThread = $env[EnvCommandCreator::ENV_TEST_IS_FIRST_ON_CHANNEL];

        if (!$process->isSuccessful()) {
            ++$this->errorCounter;
            $this->errorBuffer[$suite] = sprintf('[%s] %s', $number, $suite);
            $this->errorBuffer[$suite] .= $process->getOutput();
            $this->errorBuffer[$suite] .= $process->getErrorOutput();
        }

        $this->totalBuffer[] = new Report(
            $suite,
            $process->isSuccessful(),
            microtime(true) - $this->startTimes[$key],
            $number,
            isset($this->errorBuffer[$suite]) ? $this->errorBuffer[$suite] : null,
            (bool) $numberOnThread // @todo if EnvCommandCreator::ENV_TEST_IS_FIRST_ON_CHANNEL bool, remove this
        );
    }

    /**
     * @return Report[]
     */
    public function getReport(): array
    {
        return $this->totalBuffer;
    }
}
