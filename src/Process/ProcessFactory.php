<?php

namespace Liuggio\Fastest\Process;

use Symfony\Component\Process\Process;

class ProcessFactory
{
    private $envCommandCreator;
    private $commandToExecuteTemplate;
    private $maxParallelProcessesToExecute;

    private static $cacheBinCmd;

    public function __construct($maxParallelProcessesToExecute, $commandToExecuteTemplate = null, EnvCommandCreator $envCommandCreator = null)
    {
        if (null === $envCommandCreator) {
            $envCommandCreator = new EnvCommandCreator();
        }
        if (null === $commandToExecuteTemplate || empty($commandToExecuteTemplate)) {
            $commandToExecuteTemplate = self::getDefaultCommandToExecute();
        }
        $this->maxParallelProcessesToExecute = $maxParallelProcessesToExecute;
        $this->envCommandCreator = $envCommandCreator;
        $this->commandToExecuteTemplate = $commandToExecuteTemplate;
    }

    public function createAProcess($suite, $currentProcessorNumber, $currentProcessCounter, $isFirstOnThread = false)
    {
        $cmd = $this->replaceParameters($this->commandToExecuteTemplate, $suite, $currentProcessorNumber, $currentProcessCounter);
        $arrayEnv = $this->envCommandCreator->execute($currentProcessorNumber, $this->maxParallelProcessesToExecute, $suite, $currentProcessCounter, $isFirstOnThread);

        return $this->createProcess($cmd, $arrayEnv);
    }

    public function createAProcessForACustomCommand($execute, $currentProcessorNumber, $currentProcessCounter, $isFirstOnThread = false)
    {
        $cmd = $this->replaceParameters($execute, '', $currentProcessorNumber, $currentProcessCounter);
        $arrayEnv = $this->envCommandCreator->execute($currentProcessorNumber, $this->maxParallelProcessesToExecute, $execute, $currentProcessCounter, $isFirstOnThread);

        return $this->createProcess($cmd, $arrayEnv);
    }

    private function replaceParameters($cmd, $suite, $processNumber, $currentProcessCounter)
    {
        $commandToExecute = str_replace('{}', $suite, $cmd);
        $commandToExecute = str_replace('{p}', $processNumber, $commandToExecute);
        $commandToExecute = str_replace('{n}', $currentProcessCounter, $commandToExecute);

        return $commandToExecute;
    }

    private function createProcess($executeCommand, $arrayEnv)
    {
        $process = new Process((array)$executeCommand, null, $arrayEnv);

        $process->setTimeout(null);
        // compatibility to SF 2.2
        if (method_exists($process, 'setIdleTimeout')) {
            $process->setIdleTimeout(null);
        }

        return $process;
    }

    public static function getDefaultCommandToExecute()
    {
        if (null !== self::$cacheBinCmd) {
            return self::$cacheBinCmd;
        }

        return self::$cacheBinCmd = self::isWindows() ? self::getWindowsBinCmd() : self::getUnixBinCmd();
    }

    private static function isWindows()
    {
        return '\\' === DIRECTORY_SEPARATOR;
    }

    private static function getWindowsBinCmd()
    {
        if (file_exists(getcwd().'/vendor/bin/phpunit')) {
            return 'vendor\bin\phpunit {}';
        }

        if (file_exists(getcwd().'/bin/phpunit')) {
            return 'bin\phpunit {}';
        }

        if (file_exists(getenv('APPDATA').'\Composer\vendor\bin\phpunit')) {
            return '%APPDATA%\Composer\vendor\bin\phpunit {}';
        }

        return 'phpunit {}';
    }

    private static function getUnixBinCmd()
    {
        if (file_exists(getcwd().'/vendor/bin/phpunit')) {
            return 'vendor/bin/phpunit {}';
        }

        if (file_exists(getcwd().'/bin/phpunit')) {
            return 'bin/phpunit {}';
        }

        if (file_exists(getenv('HOME').'/.composer/vendor/bin/phpunit')) {
            return '~/.composer/vendor/bin/phpunit {}';
        }

        return 'phpunit {}';
    }
}
