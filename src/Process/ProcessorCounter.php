<?php

namespace Liuggio\Fastest\Process;

/**
 *  Number of processors seen by the OS and used for process scheduling.
 */
class ProcessorCounter
{
    const PROC_DEFAULT_NUMBER = 'unknown';

    private static $count = null;

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
        if (PHP_OS === 'Darwin') {
            $processors = system('/usr/sbin/sysctl -n hw.physicalcpu');

            if ($processors !== false && $processors) {
                return $processors;
            }
        } elseif (PHP_OS === 'Linux') {
            $file = '/proc/cpuinfo';
            if (is_file($file) && is_readable($file)) {
                try {
                    $contents = trim(file_get_contents($file));
                    return substr_count($contents, 'processor');
                } catch (\Exception $e) {
                }
            }
        }

        return self::PROC_DEFAULT_NUMBER;
    }
}
