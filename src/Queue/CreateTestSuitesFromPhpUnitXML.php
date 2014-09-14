<?php

namespace Liuggio\Fastest\Queue;

class CreateTestSuitesFromPhpUnitXML
{
    public function execute($xmlFile)
    {
        $simpleObject = $this->readFromXml($xmlFile);
        $arrayOfStrings = $this->extractSuitesFromXmlObject($simpleObject);
        $testSuites = array();
        foreach ($arrayOfStrings as $string) {
            $testSuites[] = new TestSuite($string);
        }

        return $testSuites;
    }

    private function readFromXml($filename)
    {
        if (file_exists($filename)) {
            return simplexml_load_file($filename);
        }

        exit('Failed to open $filename.');
    }

    private function extractSuitesFromXmlObject(\SimpleXMLElement $simpleObject)
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