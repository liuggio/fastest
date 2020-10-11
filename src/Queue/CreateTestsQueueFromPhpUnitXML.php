<?php

namespace Liuggio\Fastest\Queue;

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
}
