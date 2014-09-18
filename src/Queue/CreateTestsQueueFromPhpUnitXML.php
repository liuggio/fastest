<?php

namespace Liuggio\Fastest\Queue;

class CreateTestsQueueFromPhpUnitXML
{
    public static function execute($xmlFile)
    {
        $simpleObject = self::readFromXml($xmlFile);
        $arrayOfStrings = self::extractSuitesFromXmlObject($simpleObject);
        $testSuites = new TestsQueue();

        foreach ($arrayOfStrings as $string) {
            $testSuites->add($string);
        }

        return $testSuites;
    }

    private static function readFromXml($filename)
    {
        if (file_exists($filename)) {
            return simplexml_load_file($filename);
        }

        throw new \Exception('Failed to open $filename.');
    }

    private static function extractSuitesFromXmlObject(\SimpleXMLElement $simpleObject)
    {
        $directories = null;
        if (isset($simpleObject->testsuites)
            && isset($simpleObject->testsuites->testsuite)
            && isset($simpleObject->testsuites->testsuite->directory)
        ) {
            $directories = $simpleObject->testsuites->testsuite->directory;
        }

        return $directories;
    }
}
