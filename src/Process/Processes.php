<?php

namespace Liuggio\Fastest\Process;

use Symfony\Component\Process\Process;

class Processes
{
    private $processes;
    private $totalBuffer;
    private $errorBuffer;
    private $errorCounter;

    public function __construct(array $processes)
    {
        $this->processes = $processes;
        $this->totalBuffer = array();
        $this->errorBuffer = array();
        $this->errorCounter = 0;
    }

    public function cleanUP()
    {
        foreach ($this->processes as $key => $process) {
            if (null !== $process && $process->isTerminated()) {
                $this->moveToCompletedProcesses($process);
                $this->processes[$key] = null;
            }
        }
    }

    public function getIndexesOfCompletedChannel()
    {
        $indexes = array();
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
            $this->moveToCompletedProcesses($this->processes[$key]);
            $this->processes[$key] = null;
        }

        $this->processes[$key] = $process;
    }

    public function start($key = null)
    {
        if (null !== $key) {
            $this->processes[$key]->start();

            return true;
        }

        return array_walk($this->processes, function (Process $item = null) {
            if (null !== $item) {
                $item->start();
            }
        });
    }

    public function stop()
    {
        return array_walk($this->processes, function (Process $item = null) {
            if (null !== $item) {
                $item->stop();
            }
        });
    }

    public function wait()
    {
        $ret = array_walk($this->processes, function (Process $item = null) {
            if (null !== $item) {
                $item->wait();
            }
        });
        $this->cleanUP();

        return $ret;
    }

    public function count()
    {
        return count($this->processes);
    }

    public function get($index)
    {
        return $this->processes[$index];
    }

    public function isAnyStillRunning()
    {
        $noOneIsRunning = true;

        foreach ($this->processes as $process) {
            $noOneIsRunning = $noOneIsRunning && (null === $process || $process->isTerminated());
        }

        return !$noOneIsRunning;
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

    private function moveToCompletedProcesses(Process $process)
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

        $this->totalBuffer[] = new Report($suite, $process->isSuccessful(), $number, isset($this->errorBuffer[$suite]) ? $this->errorBuffer[$suite] : null, $numberOnThread);
    }

    /**
     * @return Report[]
     */
    public function getReport()
    {
        return $this->totalBuffer;
    }
}
