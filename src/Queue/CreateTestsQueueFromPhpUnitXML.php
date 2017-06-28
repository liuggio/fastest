<?php

namespace Liuggio\Fastest\Queue;

// BC
if (class_exists('\PHPUnit_Util_Configuration')) {
    class_alias('\PHPUnit_Util_Configuration', '\PHPUnit\Util\Configuration');
}

class CreateTestsQueueFromPhpUnitXML
{
    public static function execute($xmlFile)
    {
        $configuration = \PHPUnit\Util\Configuration::getInstance($xmlFile);
        $testSuites = new TestsQueue();

        /** @var \PHPUnit\Framework\TestSuite $bla */
        foreach ($configuration->getTestSuiteConfiguration()->getIterator() as $testSuite)
        {
            $class = new \ReflectionClass($testSuite->getName());
            $testSuites->add($class->getFileName());
        }

        return $testSuites;
    }
}
