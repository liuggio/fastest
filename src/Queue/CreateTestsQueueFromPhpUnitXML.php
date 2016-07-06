<?php

namespace Liuggio\Fastest\Queue;

class CreateTestsQueueFromPhpUnitXML
{
    public static function execute($xmlFile)
    {
        $configuration = \PHPUnit_Util_Configuration::getInstance($xmlFile);
        $testSuites = new TestsQueue();

        /** @var \PHPUnit_Framework_TestSuite $bla */
        foreach ($configuration->getTestSuiteConfiguration()->getIterator() as $testSuite)
        {
            $class = new \ReflectionClass($testSuite->getName());
            $testSuites->add($class->getFileName());
        }

        return $testSuites;
    }
}
