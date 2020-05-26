<?php

namespace Liuggio\Fastest\Process;

use Symfony\Component\Process\Process;

/**
 *  Number of processors seen by the OS and used for process scheduling.
 */
class ProcessorCounter
{
    const PROC_DEFAULT_NUMBER = 4;
    const PROC_CPUINFO = '/proc/cpuinfo';

    private static $count = null;

    private $procCPUInfo;

    public function __construct($procCPUInfo = self::PROC_CPUINFO)
    {
        $this->procCPUInfo = $procCPUInfo;
    }

    public function execute()
    {
        if (null !== self::$count) {
            return self::$count;
        }
        self::$count = $this->readFromProcCPUInfo();

        return self::$count;
    }

    private function readFromProcCPUInfo()
    {
        if ($this->getOS() === 'Darwin') {
            $processors = system('/usr/sbin/sysctl -n hw.physicalcpu');

            if (false !== $processors && $processors) {
                return $processors;
            }
        } elseif ($this->getOS() === 'Linux') {
            $file = $this->procCPUInfo;
            if (is_file($file) && is_readable($file)) {
                try {
                    $contents = trim(file_get_contents($file));

                    return substr_count($contents, 'processor');
                } catch (\Exception $e) {
                }
            }
        } elseif ('\\' === DIRECTORY_SEPARATOR) {
            $command = [
                'for',
                '/F',
                '"tokens=2 delims=="',
                '%C',
                'in',
                "('wmic cpu get NumberOfLogicalProcessors /value ^| findstr NumberOfLogicalProcessors')",
                'do',
                '@echo',
                '%C'
            ];
            $process = new Process($command);
            $process->run();

            if ($process->isSuccessful() && ($numProc = (int) ($process->getOutput())) > 0) {
                return $numProc;
            }
        }

        return self::PROC_DEFAULT_NUMBER;
    }

    /**
     * @return string
     */
    public function getOS()
    {
        return PHP_OS;
    }
}
