<?php

namespace Liuggio\Fastest\Process;

use Symfony\Component\Process\Process;

class Processes
{
    private $processes;

    public function __construct(array $processes)
    {
        $this->processes = $processes;
    }

    public function start()
    {
        return array_walk($this->processes, function ($item, $key) {
            $item->start();
        });
    }

    public function stop()
    {
        return array_walk($this->processes, function ($item) {
            $item->stop();
        });
    }

    public function wait()
    {
        return array_walk($this->processes, function ($item, $key) {
            $item->wait();
        });
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
            $noOneIsRunning = $noOneIsRunning && $process->isTerminated();
        }

        return !$noOneIsRunning;
    }

    /**
     * Return the biggest exit code among the processes.
     *
     * @return int
     */
    public function getExitCode()
    {
        $exitCode = 0;
        foreach ($this->processes as $process) {
            $exitCode = $this->returnExitCodeAs($process->getExitCode(), $exitCode);
        }

        return $exitCode;
    }

    /**
     * Checks if the process ended successfully.
     *
     * @return bool    true if the process ended successfully, false otherwise
     *
     * @api
     */
    public function isSuccessful()
    {
        return 0 === $this->getExitCode();
    }


    public function countIncrementalErrors()
    {
        $errorNumber = 0;
        foreach ($this->processes as $process) {

            $output = $process->getIncrementalOutput();
            $errorOutput = $process->getIncrementalErrorOutput();

            $errorCount = $this->doCountErrors($output);

            if ($errorCount > 0 || strlen($errorOutput) > 0) {
                if ($errorCount > 0) {
                    $errorNumber = $errorNumber+$errorCount;
                } else {
                    $errorNumber++;
                }
            }
        }

        return $errorNumber;
    }

    public function getErrorOutput()
    {
        $errorOutputFinal = '';
        foreach ($this->processes as $process) {

            $output = $process->getOutput();
            $errorOutput = $process->getErrorOutput();

            $errorCount = $this->doCountErrors($output);

            if ($errorCount > 0 || strlen($errorOutput) > 0) {
                $errorOutputFinal .= $output.PHP_EOL.$errorOutput;
            }
        }

        return $errorOutputFinal;
    }

    public function getProcesses()
    {
        return $this->processes;
    }

    private function doCountErrors($output)
    {
        return substr_count($output, '| x Suite Error ');
    }

    private function returnExitCodeAs($past, $current)
    {
        if ((int) $past !=0 || (int) $current != 0) {
            return max((int) $past, (int) $current);
        }

        return 0;
    }
}
