<?php

namespace Liuggio\Fastest\Queue;

use PHPUnit\Framework\TestCase;

class ReadFromInputAndPushIntoTheQueueTest extends TestCase
{
    /**
     * @test
     */
    public function shouldPushIntoTheQueueTheXMLFile(): void
    {
        $directory = __DIR__.'/Infrastructure/';
        $files = ['InMemoryQueueFactoryTest.php', 'InMemoryQueueTest.php'];

        $processesDir = str_replace('/Queue', '', __DIR__).'/Process/';
        $processesFiles = [
            'EnvCommandCreatorTest.php',
            'ProcessFactoryTest.php',
            'ProcessesManagerTest.php',
            'ProcessesTest.php',
            'ProcessorCounterTest.php',
        ];

        $assertion = new TestsQueue();

        foreach ($files as $file) {
            $assertion->add($directory.$file);
        }

        foreach ($processesFiles as $file) {
            $assertion->add($processesDir.$file);
        }

        $queue = $this->createMock('\Liuggio\Fastest\Queue\QueueInterface');
        $queue->expects($this->once())
            ->method('push')
            ->with($assertion);

        $factory = $this->getMockBuilder('\Liuggio\Fastest\Queue\QueueFactoryInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $factory
            ->expects($this->once())
            ->method('create')
            ->willReturn($queue);

        $reader = new ReadFromInputAndPushIntoTheQueue($factory);

        $ret = $reader->execute(__DIR__.'/Fixture/phpunit.xml.dist', true);

        $this->assertEquals($queue, $ret);
    }
}
