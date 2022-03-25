<?php

namespace Liuggio\Fastest\Queue;

class CreateTestsQueueFromPhpUnitXML
{
    public static function execute(string $xmlFile): TestsQueue
    {
        // phpunit 8.5 compatibility
        if (class_exists('\PHPUnit\Util\Configuration')) {
            $configuration = \PHPUnit\Util\Configuration::getInstance($xmlFile);
            $testSuites = new TestsQueue();

            $config = $configuration->getPHPUnitConfiguration();
            $filename = isset($config['bootstrap']) ? $config['bootstrap'] : 'vendor/autoload.php';

            \PHPUnit\Util\FileLoader::checkAndLoad($filename);

            self::processTestSuites($testSuites, $configuration->getTestSuiteConfiguration()->getIterator());

            return $testSuites;
        }

        // phpunit < 9.3 compatibility
        if (class_exists('\PHPUnit\TextUI\Configuration\Registry') && class_exists('\PHPUnit\TextUI\Configuration\TestSuiteMapper')) {
            $configuration = \PHPUnit\TextUI\Configuration\Registry::getInstance()->get($xmlFile);
            $testSuites = new TestsQueue();

            $PHPUnit = $configuration->phpunit();
            $filename = $PHPUnit->hasBootstrap() ? $PHPUnit->bootstrap() : 'vendor/autoload.php';

            \PHPUnit\Util\FileLoader::checkAndLoad($filename);

            $testSuite = (new \PHPUnit\TextUI\Configuration\TestSuiteMapper)->map($configuration->testSuite(), '');
            self::processTestSuites($testSuites, $testSuite->getIterator());

            return $testSuites;
        }

        // phpunit 9.3 compatibility
        if (class_exists('\PHPUnit\TextUI\XmlConfiguration\Loader') && class_exists('\PHPUnit\TextUI\XmlConfiguration\TestSuiteMapper')) {
            $configuration = (new \PHPUnit\TextUI\XmlConfiguration\Loader)->load($xmlFile);

            $testSuites = new TestsQueue();

            $PHPUnit = $configuration->phpunit();
            $filename = $PHPUnit->hasBootstrap() ? $PHPUnit->bootstrap() : 'vendor/autoload.php';

            \PHPUnit\Util\FileLoader::checkAndLoad($filename);
            $testSuiteCollection = $configuration->testSuite();

            $testSuite = (new \PHPUnit\TextUI\XmlConfiguration\TestSuiteMapper)->map($testSuiteCollection, '');
            $iterator = $testSuite->getIterator();

            self::processTestSuites($testSuites, $iterator);

            return $testSuites;
        }

        // phpunit > 9.3 compatibility
        if (class_exists('\PHPUnit\TextUI\XmlConfiguration\Loader') && class_exists('\PHPUnit\TextUI\TestSuiteMapper')) {
            $configuration = (new \PHPUnit\TextUI\XmlConfiguration\Loader)->load($xmlFile);

            $testSuites = new TestsQueue();

            $PHPUnit = $configuration->phpunit();
            $filename = $PHPUnit->hasBootstrap() ? $PHPUnit->bootstrap() : 'vendor/autoload.php';

            \PHPUnit\Util\FileLoader::checkAndLoad($filename);
            $testSuiteCollection = $configuration->testSuite();

            $testSuite = (new \PHPUnit\TextUI\TestSuiteMapper)->map($testSuiteCollection, '');
            $iterator = $testSuite->getIterator();

            self::processTestSuites($testSuites, $iterator);

            return $testSuites;
        }

        return new TestsQueue();
    }

    /**
     * @param TestsQueue $testSuites
     * @param \Iterator<\PHPUnit\Framework\TestSuite|\PHPUnit\Framework\TestCase> $testSuiteIterator
     *
     * @throws \ReflectionException
     */
    private static function processTestSuites(
        TestsQueue $testSuites,
        \Iterator $testSuiteIterator
    ): void {
        foreach ($testSuiteIterator as $testSuite) {
            self::addTestFile($testSuites, $testSuite);

            if ($testSuite instanceof \PHPUnit\Framework\TestSuite) {
                self::processTestSuites($testSuites, $testSuite->getIterator());
            }
        }
    }

    /**
     * @param TestsQueue $testSuites
     * @param \PHPUnit\Framework\TestSuite<\PHPUnit\Framework\Test>|\PHPUnit\Framework\TestCase $testSuite
     *
     * @throws \ReflectionException
     */
    private static function addTestFile(TestsQueue $testSuites, $testSuite): void
    {
        $name = $testSuite->getName();
        if (class_exists($name)) {
            $class = new \ReflectionClass($name);
            if (false === $fileName = $class->getFileName()) {
                return;
            }

            $testSuites->add($fileName);
        }
    }
}
