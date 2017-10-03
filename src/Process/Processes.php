<?php

namespace Liuggio\Fastest\Process;

use Symfony\Component\Process\Process;

class Processes
{
    /**
     * @var Process[]
     */
    private $processes;

    /**
     * @var float[] Unix timestamp with float part of processes start times
     */
    private $startTimes;
    
    /**
     * @var Report[]
     */
    private $totalBuffer;

    private $errorBuffer;
    private $errorCounter;

    public function __construct(array $processes)
    {
        $this->processes = $processes;
        $this->totalBuffer = [];
        $this->errorBuffer = [];
        $this->startTimes = [];
        $this->errorCounter = 0;
    }

    public function cleanUP()
    {
        foreach ($this->processes as $key => $process) {
            if (null !== $process && $process->isTerminated()) {
                $this->moveToCompletedProcesses($key, $process);
                $this->processes[$key] = null;
                $this->startTimes[$key] = null;
            }
        }
    }

    public function getIndexesOfCompletedChannel()
    {
        $indexes = [];
        foreach ($this->processes as $index => $process) {
            if (null === $process || $process->isTerminated()) {
                $indexes[] = $index;
            }
        }

        return $indexes;
    }

    public function add($key, Process $process)
    {
        $this->cleanUP();
        if (isset($this->processes[$key]) && null !== $this->processes[$key]) {
            $this->assertTerminated($key);
            $this->moveToCompletedProcesses($key, $this->processes[$key]);
            $this->processes[$key] = null;
        }

        $this->processes[$key] = $process;
    }

    public function start($key = null)
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

    /**
     * @param $key
     */
    private function startProcess($key)
    {
        $this->startTimes[$key] = microtime(true);
        $this->processes[$key]->start();
    }

    public function stop()
    {
        return array_walk($this->processes, function (Process $item = null) {
            if (null !== $item) {
                $item->stop();
            }
        });
    }

    /**
     * @param callable $terminationCallback A callback to be called after one of the processes is terminated
     * @return bool
     */
    public function wait($terminationCallback = null)
    {
        $lastProcessesRunningCount = $currentRunningProcessesCount = $this->countRunning();
        while ($currentRunningProcessesCount > 0) {
            $currentRunningProcessesCount = $this->countRunning();
            if ($lastProcessesRunningCount !== $currentRunningProcessesCount) {
                $lastProcessesRunningCount = $currentRunningProcessesCount;
                $this->cleanUP();
                if ($terminationCallback !== null) {
                    call_user_func($terminationCallback);
                }
            }
            usleep(1000);
        }
        return true;
    }

    public function count()
    {
        return count($this->processes);
    }

    public function get($index)
    {
        return $this->processes[$index];
    }

    /**
     * @return int Number of processes still running
     */
    public function countRunning()
    {
        $count = 0;

        foreach ($this->processes as $process) {
            if (null !== $process && !$process->isTerminated()) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Return the number of tests failing, 0 if none :)
     *
     * @return int
     */
    public function getExitCode()
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
    public function isSuccessful()
    {
        return 0 === $this->getExitCode();
    }

    public function countErrors()
    {
        return $this->errorCounter;
    }

    public function getErrorOutput()
    {
        return $this->errorBuffer;
    }

    /**
     * @throws LogicException
     */
    private function assertTerminated($key)
    {
        if (!$this->processes[$key]->isTerminated()) {
            throw new LogicException('Process must be terminated before calling');
        }
    }

    /**
     * @param $key
     * @param Process $process
     */
    private function moveToCompletedProcesses($key, Process $process)
    {
        $env = $process->getEnv();
        $suite = str_replace(EnvCommandCreator::ENV_TEST_ARGUMENT.'=', '', $env[3]);
        $number = str_replace(EnvCommandCreator::ENV_TEST_CHANNEL.'=', '', $env[0]);
        $numberOnThread = (int) str_replace(EnvCommandCreator::ENV_TEST_IS_FIRST_ON_CHANNEL.'=', '', $env[5]);

        if (!$process->isSuccessful()) {
            $this->errorCounter++;
            $this->errorBuffer[$suite] = sprintf("[%s] %s", $number, $suite);
            $this->errorBuffer[$suite] .= $process->getOutput();
            $this->errorBuffer[$suite] .= $process->getErrorOutput();
        }

        $this->totalBuffer[] = new Report(
            $suite,
            $process->isSuccessful(),
            microtime(true) - $this->startTimes[$key],
            $number,
            isset($this->errorBuffer[$suite]) ? $this->errorBuffer[$suite] : null,
            $numberOnThread
        );
    }

    /**
     * @return Report[]
     */
    public function getReport()
    {
        return $this->totalBuffer;
    }
}
