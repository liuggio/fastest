<?php

namespace Liuggio\Fastest\Process;

use Symfony\Component\Process\Process;

class ProcessFactory
{
    /**
     * @var EnvCommandCreator
     */
    private $envCommandCreator;

    /**
     * @var string
     */
    private $commandToExecuteTemplate;

    /**
     * @var int
     */
    private $maxParallelProcessesToExecute;

    /**
     * @var string|null
     */
    private static $cacheBinCmd;

    public function __construct(
        int $maxParallelProcessesToExecute,
        string $commandToExecuteTemplate = null,
        EnvCommandCreator $envCommandCreator = null
    ) {
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

    public function createAProcess(
        string $testPath,
        int $currentProcessorNumber,
        int $currentProcessCounter,
        bool $isFirstOnThread = false
    ): Process {
        $cmd = $this->replaceParameters($this->commandToExecuteTemplate, $testPath, $currentProcessorNumber, $currentProcessCounter);
        $arrayEnv = $this->envCommandCreator->execute(
            $currentProcessorNumber,
            $this->maxParallelProcessesToExecute,
            $testPath,
            $currentProcessCounter,
            $isFirstOnThread
        );

        return $this->createProcess($cmd, $arrayEnv);
    }

    public function createAProcessForACustomCommand(
        string $execute,
        int $currentProcessorNumber,
        int $currentProcessCounter,
        bool $isFirstOnThread = false
    ): Process {
        $cmd = $this->replaceParameters($execute, '', $currentProcessorNumber, $currentProcessCounter);
        $arrayEnv = $this->envCommandCreator->execute(
            $currentProcessorNumber,
            $this->maxParallelProcessesToExecute,
            $execute,
            $currentProcessCounter,
            $isFirstOnThread
        );

        return $this->createProcess($cmd, $arrayEnv);
    }

    private function replaceParameters(string $cmd, string $suite, int $processNumber, int $currentProcessCounter): string
    {
        $commandToExecute = str_replace('{}', $suite, $cmd);
        $commandToExecute = str_replace('{p}', (string) $processNumber, $commandToExecute);
        $commandToExecute = str_replace('{n}', (string) $currentProcessCounter, $commandToExecute);

        return $commandToExecute;
    }

    /**
     * @param string $executeCommand
     * @param array<string, mixed> $arrayEnv
     *
     * @return Process
     */
    private function createProcess(string $executeCommand, array $arrayEnv): Process
    {
        $process = Process::fromShellCommandline($executeCommand, null, $arrayEnv);
        $process->setTimeout(null);
        $process->setIdleTimeout(null);

        return $process;
    }

    public static function getDefaultCommandToExecute(): string
    {
        if (null !== self::$cacheBinCmd) {
            return self::$cacheBinCmd;
        }

        return self::$cacheBinCmd = self::isWindows() ? self::getWindowsBinCmd() : self::getUnixBinCmd();
    }

    private static function isWindows(): bool
    {
        return '\\' === DIRECTORY_SEPARATOR;
    }

    private static function getWindowsBinCmd(): string
    {
        if (file_exists(getcwd().'/vendor/bin/phpunit')) {
            return 'vendor\bin\phpunit {}';
        }

        if (file_exists(getcwd().'/bin/phpunit')) {
            return 'bin\phpunit {}';
        }

        /** @var string $appData */
        $appData = getenv('APPDATA');
        if (file_exists($appData.'\Composer\vendor\bin\phpunit')) {
            return '%APPDATA%\Composer\vendor\bin\phpunit {}';
        }

        return 'phpunit {}';
    }

    private static function getUnixBinCmd(): string
    {
        if (file_exists(getcwd().'/vendor/bin/phpunit')) {
            return 'vendor/bin/phpunit {}';
        }

        if (file_exists(getcwd().'/bin/phpunit')) {
            return 'bin/phpunit {}';
        }

        /** @var string $home */
        $home = getenv('HOME');
        if (file_exists($home.'/.composer/vendor/bin/phpunit')) {
            return '~/.composer/vendor/bin/phpunit {}';
        }

        return 'phpunit {}';
    }
}
