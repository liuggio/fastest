<?php

namespace Liuggio\Fastest\Process;

class EnvCommandCreator
{
    const ENV_TEST_CHANNEL = 'ENV_TEST_CHANNEL';
    const ENV_TEST_CHANNEL_READABLE = 'ENV_TEST_CHANNEL_READABLE';
    const ENV_TEST_CHANNELS_NUMBER = 'ENV_TEST_CHANNELS_NUMBER';
    const ENV_TEST_ARGUMENT = 'ENV_TEST_ARGUMENT';
    const ENV_TEST_INCREMENTAL_NUMBER = 'ENV_TEST_INC_NUMBER';
    const ENV_TEST_IS_FIRST_ON_CHANNEL = 'ENV_TEST_IS_FIRST_ON_CHANNEL';

    /**
     * @param int $i
     * @param int $maxProcesses
     * @param string $test
     * @param int $currentProcessCounter
     * @param bool $isFirstOnItsThread
     *
     * @return array<string, mixed>
     */
    public function execute(
        int $i,
        int $maxProcesses,
        string $test,
        int $currentProcessCounter,
        bool $isFirstOnItsThread = false
    ): array {
        $env = [
            self::ENV_TEST_CHANNEL => $i,
            self::ENV_TEST_CHANNEL_READABLE => 'test_'.$i,
            self::ENV_TEST_CHANNELS_NUMBER => $maxProcesses,
            self::ENV_TEST_ARGUMENT => $test,
            self::ENV_TEST_INCREMENTAL_NUMBER => $currentProcessCounter,
            self::ENV_TEST_IS_FIRST_ON_CHANNEL => (int) $isFirstOnItsThread, // @todo should this be bool?
        ] + $_SERVER + $_ENV;

        $res = [];
        $mergedArgs = [];
        foreach ($env as $key => $value) {
            if (array_key_exists(strtolower($key), $mergedArgs)) {
                continue;
            }

            if (!is_array($value)) {
                $formattedValue = self::formatValueForEnv($value);
                if (null !== $formattedValue) {
                    $res[$key] = $value;
                    $mergedArgs[strtolower($key)] = true;
                }
            } else {
                self::decomposeRecursively($key, $value, $res, $mergedArgs);
            }
        }

        return $res;
    }

    /**
     * @param array<mixed> $source
     * @param array<mixed> $globalArray
     * @param array<mixed> $mergedArgs
     */
    public static function decomposeRecursively(string $mainKey, array $source, array &$globalArray, array &$mergedArgs): void
    {
        foreach ($source as $childKey => $value) {
            $newKey = $mainKey . '_' . $childKey;
            if (is_array($value)) {
                self::decomposeRecursively($newKey, $value, $globalArray, $mergedArgs);
            } elseif (!array_key_exists($newKey, $globalArray)) {
                $formattedValue = self::formatValueForEnv($value);
                if (null !== $formattedValue) {
                    $globalArray[$newKey] = $formattedValue;
                    $mergedArgs[strtolower($newKey)] = true;
                }
            }
        }
    }

    public static function formatValueForEnv(mixed $value): ?string
    {
        if (is_scalar($value)) {
            return (string) $value;
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        }

        return null;
    }
}
