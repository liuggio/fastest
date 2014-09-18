<?php

namespace Liuggio\Fastest\Process;

/**
 *  Number of processors seen by the OS and used for process scheduling.
 */
class ProcessorCounter
{
    const PROC_CPUINFO = '/proc/cpuinfo';

    private $procCPUInfo;

    public function __construct($procCPUInfo = self::PROC_CPUINFO)
    {
        $this->procCPUInfo = $procCPUInfo;
    }

    public function execute()
    {
        return $this->readFromProcCPUInfo();
    }

    private function readFromProcCPUInfo()
    {
        $file = $this->procCPUInfo;
        if (!is_file($file) || !is_readable($file)) {
            return null;
        }
        $contents = trim(@file_get_contents($file));

        return substr_count($contents, 'processor');
    }
}
