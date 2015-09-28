<?php

namespace Liuggio\Fastest;

/**
 *  Number of processors seen by the OS and used for process scheduling.
 */
class ProcessorCounter
{
    const PROC_DEFAULT_NUMBER = 4;
    const PROC_CPUINFO = '/proc/cpuinfo';

    /**
     * @var int|null
     */
    private static $count = null;

    /**
     * @var string
     */
    private $procCPUInfo;

    /**
     * @var string
     */
    private $os;

    /**
     * @param string $procCPUInfo
     * @param string $os
     */
    public function __construct($procCPUInfo = self::PROC_CPUINFO, $os = PHP_OS)
    {
        $this->procCPUInfo = $procCPUInfo;
        $this->os = $os;
    }

    /**
     * @return int
     */
    public function execute()
    {
        if (null !== self::$count) {
            return self::$count;
        }
        self::$count = $this->readFromProcCPUInfo();

        return self::$count;
    }

    /**
     * @return int
     */
    private function readFromProcCPUInfo()
    {
        if ($this->os === 'Darwin') {
            $processors = system('/usr/sbin/sysctl -n hw.physicalcpu');
            if ($processors !== false && $processors) {
                return $processors;
            }
        } elseif ($this->os === 'Linux') {
            if (is_file($this->procCPUInfo) && is_readable($this->procCPUInfo)) {
                try {
                    $contents = trim(file_get_contents($this->procCPUInfo));

                    return substr_count($contents, 'processor');
                } catch (\Exception $e) {
                }
            }
        }

        return self::PROC_DEFAULT_NUMBER;
    }
}
