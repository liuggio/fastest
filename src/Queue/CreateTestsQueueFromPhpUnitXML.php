<?php

namespace Liuggio\Fastest\Queue;

// BC
if (class_exists('\PHPUnit_Util_Configuration')) {
    class_alias('\PHPUnit_Util_Configuration', '\PHPUnit\Util\Configuration');
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

if (class_exists('\PHPUnit_Runner_Filter_Factory')) {
    class_alias('\PHPUnit_Runner_Filter_Factory', '\PHPUnit\Runner\Filter\Factory');
}

if (class_exists('\PHPUnit_Runner_Filter_ExcludeGroupFilterIterator')) {
    class_alias('\PHPUnit_Runner_Filter_ExcludeGroupFilterIterator', '\PHPUnit\Runner\Filter\ExcludeGroupFilterIterator');
}

if (class_exists('\PHPUnit_Runner_Filter_IncludeGroupFilterIterator')) {
    class_alias('\PHPUnit_Runner_Filter_IncludeGroupFilterIterator', '\PHPUnit\Runner\Filter\IncludeGroupFilterIterator');
}

/*
 * Trigger autoload for possible file loader versions.
 * This fixes the problem with PHP classes being case insensitive versus composer case sensitive autoloader.
 */
class_exists('\PHPUnit\Util\Fileloader');
class_exists('\PHPUnit\Util\FileLoader');

class CreateTestsQueueFromPhpUnitXML
{
    public static function execute($xmlFile)
    {
        $configuration = \PHPUnit\Util\Configuration::getInstance($xmlFile);
        $testSuites = new TestsQueue();

        self::handleBootstrap($configuration->getPHPUnitConfiguration());
        $testSuite = static::filterTestSuite($configuration);
        self::processTestSuite($testSuites, $testSuite->getIterator());

        return $testSuites;
    }

    private static function filterTestSuite(
        \PHPUnit\Util\Configuration $configuration
    ) {
        $testSuite = $configuration->getTestSuiteConfiguration();
        $groupConfiguration = $configuration->getGroupConfiguration();

        if (empty($groupConfiguration['exclude']) && empty($groupConfiguration['include'])) {
            return $testSuite;
        }

        $filterFactory = new \PHPUnit\Runner\Filter\Factory();

        if (!empty($groupConfiguration['exclude'])) {
            $filterFactory->addFilter(
                new \ReflectionClass(\PHPUnit\Runner\Filter\ExcludeGroupFilterIterator::class),
                $groupConfiguration['exclude']
            );
        }

        if (!empty($groupConfiguration['include'])) {
            $filterFactory->addFilter(
                new \ReflectionClass(\PHPUnit\Runner\Filter\IncludeGroupFilterIterator::class),
                $groupConfiguration['include']
            );
        }

        $testSuite->injectFilter($filterFactory);

        return $testSuite;
    }

    private static function processTestSuite(
        TestsQueue $testSuites,
        \RecursiveIterator $testSuiteIterator
    ) {
        foreach ($testSuiteIterator as $testSuite) {
            if (0 === $testSuite->count()) {
                continue;
            }

            self::addTestFile($testSuites, $testSuite);

            if ($testSuite instanceof \PHPUnit\Framework\TestSuite) {
                self::processTestSuite($testSuites, $testSuite->getIterator());
            }
        }
    }

    private static function addTestFile(TestsQueue $testSuites, $testSuite)
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
