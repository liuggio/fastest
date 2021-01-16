<?php

namespace Liuggio\Fastest\Queue;

class CreateTestsQueueFromPhpUnitXML
{
    public static function execute(string $xmlFile): TestsQueue
    {
        // phpunit 9.0 compatibility
        if (class_exists('\PHPUnit\TextUI\Configuration\Configuration')) {
            $configuration = \PHPUnit\TextUI\Configuration\Registry::getInstance()->get($xmlFile);

            $testSuites = new TestsQueue();

            self::handlePhpUnitBootstrapV90($configuration->phpunit());
            self::processTestSuiteCollectionV90($testSuites, $configuration->testSuite());

            return $testSuites;
        }

        // phpunit 9.3 compatibility
        if (class_exists('\PHPUnit\TextUI\XmlConfiguration\Configuration')) {
            $configuration = (new \PHPUnit\TextUI\XmlConfiguration\Loader)->load($xmlFile);

            $testSuites = new TestsQueue();

            self::handlePhpUnitBootstrapV93($configuration->phpunit());
            self::processTestSuiteCollectionV93($testSuites, $configuration->testSuite());

            return $testSuites;
        }

        $configuration = \PHPUnit\Util\Configuration::getInstance($xmlFile);
        $testSuites = new TestsQueue();

        self::handleBootstrap($configuration->getPHPUnitConfiguration());
        self::processTestSuite($testSuites, $configuration->getTestSuiteConfiguration()->getIterator());

        return $testSuites;
    }

    /**
     * @param TestsQueue $testSuites
     * @param \Iterator<\PHPUnit\Framework\TestSuite|\PHPUnit\Framework\TestCase> $testSuiteIterator
     *
     * @throws \ReflectionException
     */
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

    private static function processTestSuiteCollectionV90(
        TestsQueue $testSuites,
        \PHPUnit\TextUI\Configuration\TestSuiteCollection $testSuiteCollection
    ): void {
        $testSuite = (new \PHPUnit\TextUI\Configuration\TestSuiteMapper)->map($testSuiteCollection, '');
        self::processTestSuite($testSuites, $testSuite->getIterator());
    }

    private static function processTestSuiteCollectionV93(
        TestsQueue $testSuites,
        \PHPUnit\TextUI\XmlConfiguration\TestSuiteCollection $testSuiteCollection
    ): void {
        // phpunit 9.5 compatibility
        if (class_exists('\PHPUnit\TextUI\TestSuiteMapper')) {
            $testSuite = (new \PHPUnit\TextUI\TestSuiteMapper)->map($testSuiteCollection, '');
        } else {
            $testSuite = (new \PHPUnit\TextUI\XmlConfiguration\TestSuiteMapper)->map($testSuiteCollection, '');
        }
        self::processTestSuite($testSuites, $testSuite->getIterator());
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

    /**
     * @param array<string, bool|string|int> $config
     *
     * @return void
     */
    private static function handleBootstrap(array $config): void
    {
        /** @var string $filename */
        $filename = isset($config['bootstrap']) ? $config['bootstrap'] : 'vendor/autoload.php';

        \PHPUnit\Util\FileLoader::checkAndLoad($filename);
    }

    /**
     * Loads a bootstrap file.
     *
     * @param \PHPUnit\TextUI\Configuration\PHPUnit $PHPUnit The Phpunit config
     */
    private static function handlePhpUnitBootstrapV90(\PHPUnit\TextUI\Configuration\PHPUnit $PHPUnit): void
    {
        $filename = $PHPUnit->hasBootstrap() ? $PHPUnit->bootstrap() : 'vendor/autoload.php';

        \PHPUnit\Util\FileLoader::checkAndLoad($filename);
    }

    /**
     * Loads a bootstrap file.
     *
     * @param \PHPUnit\TextUI\XmlConfiguration\PHPUnit $PHPUnit The Phpunit config
     */
    private static function handlePhpUnitBootstrapV93(\PHPUnit\TextUI\XmlConfiguration\PHPUnit $PHPUnit): void
    {
        $filename = $PHPUnit->hasBootstrap() ? $PHPUnit->bootstrap() : 'vendor/autoload.php';

        \PHPUnit\Util\FileLoader::checkAndLoad($filename);
    }
}
