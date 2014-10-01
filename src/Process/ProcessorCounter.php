<?php

namespace Liuggio\Fastest\Process;

/**
 *  Number of processors seen by the OS and used for process scheduling.
 */
class ProcessorCounter
{
    const PROC_CPUINFO = '/proc/cpuinfo';
    const PROC_DEFAULT_NUMBER = 4;

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
        $file = $this->procCPUInfo;
        if (!is_file($file) || !is_readable($file)) {
            return self::PROC_DEFAULT_NUMBER;
        }
        try {
            $contents = trim(file_get_contents($file));
        } catch (\Exception $e) {
            return self::PROC_DEFAULT_NUMBER;
        }

        return substr_count($contents, 'processor');
    }
}
