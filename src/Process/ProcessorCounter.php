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

    /**
     * @var int|null
     */
    private static $count = null;

    /**
     * @var string
     */
    private $procCPUInfo;

    public function __construct(string $procCPUInfo = self::PROC_CPUINFO)
    {
        $this->procCPUInfo = $procCPUInfo;
    }

    public function execute(): int
    {
        if (null !== self::$count) {
            return self::$count;
        }
        self::$count = $this->readFromProcCPUInfo();

        return self::$count;
    }

    private function readFromProcCPUInfo(): int
    {
        if ($this->getOS() === 'Darwin') {
            $processors = system('/usr/sbin/sysctl -n hw.physicalcpu');

            if (false !== $processors && $processors) {
                return (int) $processors;
            }
        } elseif ($this->getOS() === 'Linux') {
            $file = $this->procCPUInfo;
            if (is_file($file) && is_readable($file)) {
                try {
                    $fileContent = file_get_contents($file);
                    if (false !== $fileContent) {
                        return (int) substr_count(trim($fileContent), 'processor');
                    }
                } catch (\Exception $e) {
                }
            }
        } elseif ('\\' === DIRECTORY_SEPARATOR) {
            $executeCommand = 'for /F "tokens=2 delims==" %C in (\'wmic cpu get NumberOfLogicalProcessors /value ^| findstr NumberOfLogicalProcessors\') do @echo %C';
            $process = Process::fromShellCommandline($executeCommand);
            $process->run();

            if ($process->isSuccessful() && ($numProc = (int) ($process->getOutput())) > 0) {
                return $numProc;
            }
        }

        return self::PROC_DEFAULT_NUMBER;
    }

    public function getOS(): string
    {
        return PHP_OS;
    }
}
