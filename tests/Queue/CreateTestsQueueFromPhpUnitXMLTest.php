<?php

namespace Liuggio\Fastest\Queue;


class CreateTestsQueueFromPhpUnitXMLTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function shouldCreateAnArrayOfTestSuitesFromXML()
    {
        $output = CreateTestsQueueFromPhpUnitXML::execute(__DIR__.'/Fixture/phpunit.xml.dist');

        $directory = __DIR__ . '/Infrastructure/';
        $files = array('InMemoryQueueFactoryTest.php', 'InMemoryQueueTest.php');
        $queue = new TestsQueue();

        foreach ($files as $file) {
            $queue->add($directory . $file);
        }

        $this->assertEquals($queue, $output);
    }
}
