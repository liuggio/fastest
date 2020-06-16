<?php

namespace Liuggio\Fastest\Queue;

use PHPUnit\Framework\TestCase;

class CreateTestsQueueFromPhpUnitXMLTest extends TestCase
{
    /**
     * @test
     */
    public function shouldCreateAnArrayOfTestSuitesFromXML()
    {
        $output = CreateTestsQueueFromPhpUnitXML::execute(__DIR__.'/Fixture/phpunit.xml.dist');

        $infrastructureDir = __DIR__.'/Infrastructure/';
        $infrastructureFiles = [
            'InMemoryQueueFactoryTest.php',
            'InMemoryQueueTest.php',
        ];

        $processesDir = str_replace('/Queue', '', __DIR__).'/Process/';
        $processesFiles = [
            'ProcessFactoryTest.php',
            'ProcessesManagerTest.php',
            'ProcessesTest.php',
            'ProcessorCounterTest.php',
        ];

        $dummyDir = __DIR__.'/Fixture/tests/';
        $dummyFiles = [
            'DummyIncludedTest.php',
        ];

        $queue = new TestsQueue();

        foreach ($infrastructureFiles as $file) {
            $queue->add($infrastructureDir.$file);
        }

        foreach ($processesFiles as $file) {
            $queue->add($processesDir.$file);
        }

        foreach ($dummyFiles as $file) {
            $queue->add($dummyDir.$file);
        }

        $this->assertEquals($queue, $output);
    }
}
