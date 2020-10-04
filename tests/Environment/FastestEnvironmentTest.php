<?php

namespace Liuggio\Fastest\Environment;

use Liuggio\Fastest\Process\EnvCommandCreator;
use PHPUnit\Framework\TestCase;

class FastestEnvironmentTest extends TestCase
{
    protected function setUp(): void
    {
        $envName = EnvCommandCreator::ENV_TEST_CHANNEL_READABLE;
        putenv("$envName=");
    }

    /**
     * @test
     */
    public function shouldSetEnvironmentVariablesFromGet(): void
    {
        $envValue = 'myTestDb'.mt_rand();
        $envName = EnvCommandCreator::ENV_TEST_CHANNEL_READABLE;
        $_GET[$envName] = $envValue;
        FastestEnvironment::setFromRequest();
        $this->assertEquals($envValue, getenv($envName), 'Test that environment variable was set from GET');
    }

    /**
     * @test
     */
    public function shouldSetEnvironmentVariablesFromCookie(): void
    {
        $envValue = 'myTestDb'.mt_rand();
        $envName = EnvCommandCreator::ENV_TEST_CHANNEL_READABLE;
        $_COOKIE[$envName] = $envValue;
        FastestEnvironment::setFromRequest();
        $this->assertEquals($envValue, getenv($envName), 'Test that environment variable was set from cookie');
    }

    /**
     * @test
     */
    public function shouldSetEnvironmentVariablesFromHttpHeader(): void
    {
        $envValue = 'myTestDb'.mt_rand();
        $envName = EnvCommandCreator::ENV_TEST_CHANNEL_READABLE;
        $_SERVER["HTTP_X_FASTEST_$envName"] = $envValue;
        FastestEnvironment::setFromRequest();
        $this->assertEquals($envValue, getenv($envName), 'Test that environment variable was set from HTTP header');
    }

    /**
     * @test
     */
    public function shouldNotSetEnvironmentVariablesIfWhenNotPresentInRequest(): void
    {
        $envName = EnvCommandCreator::ENV_TEST_CHANNEL_READABLE;
        FastestEnvironment::setFromRequest();
        $this->assertEmpty(getenv($envName), 'Test that environment variable is not set if not present in request');
    }

    /**
     * @test
     */
    public function shouldRaiseExceptionWhenRequestValueIsInvalid(): void
    {
        $envValue = 'myTestDb'."\'()";
        $envName = EnvCommandCreator::ENV_TEST_CHANNEL_READABLE;
        $_GET[$envName] = $envValue;

        $this->expectException('\Liuggio\Fastest\Environment\InvalidValueException');
        FastestEnvironment::setFromRequest();
    }
}
