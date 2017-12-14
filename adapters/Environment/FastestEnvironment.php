<?php

namespace Liuggio\Fastest\Environment;

use Liuggio\Fastest\Process\EnvCommandCreator;

/**
 * Sets the fastest environment variables.
 */
class FastestEnvironment
{
    /**
     * Tries to extract the environment variable ENV_TEST_CHANNEL_READABLE from the HTTP requests:
     *      1) From GET as ENV_TEST_CHANNEL_READABLE
     *      2) From a cookie named ENV_TEST_CHANNEL_READABLE
     *      3) From a HTTP header called X-FASTEST-ENV-TEST-CHANNEL-READABLE.
     */
    public static function setFromRequest()
    {
        // Try to find if ENV_TEST_CHANNEL_READABLE is present in the request
        $testDbEnvVarName = EnvCommandCreator::ENV_TEST_CHANNEL_READABLE;
        $httpHeaderName = 'HTTP_X_FASTEST_'.$testDbEnvVarName;
        switch (true) {
            // Check request query
            case isset($_GET[$testDbEnvVarName]):
                $envVarValue = $_GET[$testDbEnvVarName];
                break;

            // Check cookie
            case isset($_COOKIE[$testDbEnvVarName]):
                $envVarValue = $_COOKIE[$testDbEnvVarName];
                break;

            // Check HTTP header
            case isset($_SERVER[$httpHeaderName]):
                $envVarValue = $_SERVER[$httpHeaderName];
                break;

            // Not found
            default:
                return;
        }

        // Avoid setting the environment variable to an unexpected value
        $regexp = '/[^a-z_0-9\-]+/i';
        if (preg_match($regexp, $envVarValue)) {
            throw new InvalidValueException("Invalid $testDbEnvVarName value: $envVarValue (must match regex '$regexp')");
        }

        // Set environment variable
        putenv("$testDbEnvVarName=$envVarValue");
    }
}
