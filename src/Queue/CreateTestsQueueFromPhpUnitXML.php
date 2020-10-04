<?php

namespace Liuggio\Fastest\Queue;

// BC
if (class_exists('\PHPUnit_Util_Configuration')) {
    class_alias('\PHPUnit_Util_Configuration', '\PHPHPUnit_Util_ConfigurationPUnit\Util\Configuration');
}

if (class_exists('\PHPUnit_Framework_TestSuite')) {
    class_alias('\PHPUnit_Framework_TestSuite', '\PHPUnit\Framework\TestSuite');
}

if (class_exists('\PHPUnit_Util_TestSuiteIterator')) {
    class_alias('\PHPUnit_Util_TestSuiteIterator', '\PHPUnit\Framework\TestSuiteIterator');
}

if (class_exists('\PHPUnit_Util_Fileloader')) {
    class_alias('\PHPUnit_Util_Fileloader', '\PHPUnit\Util\Fileloader');
}

/*
 * Trigger autoload for possible file loader versions.
 * This fixes the problem with PHP classes being case insensitive versus composer case sensitive autoloader.
 */
class_exists('\PHPUnit\Util\Fileloader');
class_exists('\PHPUnit\Util\FileLoader');

class CreateTestsQueueFromPhpUnitXML
{
    public static function execute(string $xmlFile): TestsQueue
    {
        $configuration = \PHPUnit\Util\Configuration::getInstance($xmlFile);
        $testSuites = new TestsQueue();

        self::handleBootstrap($configuration->getPHPUnitConfiguration());
        self::processTestSuite($testSuites, $configuration->getTestSuiteConfiguration()->getIterator());

        return $testSuites;
    }

    private static function processTestSuite(
        TestsQueue $testSuites,
        \Iterator $testSuiteIterator
    ): void {
        foreach ($testSuiteIterator as $testSuite) {
            self::addTestFile($testSuites, $testSuite);

            if ($testSuite instanceof \PHPUnit\Framework\TestSuite) {
                self::processTestSuite($testSuites, $testSuite->getIterator());
            }
        }
    }

    private static function addTestFile(TestsQueue $testSuites, $testSuite): void
    {
        $name = $testSuite->getName();
        if (class_exists($name)) {
            $class = new \ReflectionClass($name);
            $testSuites->add($class->getFileName());
        }
    }

    /**
     * Loads a bootstrap file.
     *
     * @param array $config The Phpunit config
     */
    private static function handleBootstrap(array $config)
    {
        $filename = isset($config['bootstrap']) ? $config['bootstrap'] : 'vendor/autoload.php';

        \PHPUnit\Util\FileLoader::checkAndLoad($filename);
    }
}
