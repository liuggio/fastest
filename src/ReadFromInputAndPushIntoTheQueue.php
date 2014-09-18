<?php
namespace Liuggio\Fastest;

use Liuggio\Fastest\Exception\QueueEmptyException;
use Liuggio\Fastest\Queue\CreateTestsQueueFromPhpUnitXML;
use Liuggio\Fastest\Queue\CreateTestsQueueFromSTDIN;
use Liuggio\Fastest\Queue\QueueFactoryInterface;
use Liuggio\Fastest\Queue\TestsQueue;

class ReadFromInputAndPushIntoTheQueue
{
    protected $queueFactory;
    protected $queuePort;

    public function __construct(QueueFactoryInterface $queueFactory)
    {
        $this->queueFactory = $queueFactory;
    }

    public function execute($xmlFile = null, $preserveOrder = false)
    {
        if (null !== $xmlFile) {
            $testsQueue = CreateTestsQueueFromPhpUnitXML::execute($xmlFile);
        } else {
            $readFromPipe = new CreateTestsQueueFromSTDIN();
            $testsQueue = $readFromPipe->execute();
        }

        $this->assertTestsQueueIsNotEmpty($testsQueue);

        if (!$preserveOrder) {
            $testsQueue = $testsQueue->randomize();
        }

        $queue = $this->queueFactory->createForProducer();

        $queue->push($testsQueue);

        return $queue;
    }

    private function assertTestsQueueIsNotEmpty(TestsQueue $testsQueue)
    {
        if ($testsQueue->isEmpty()) {
            throw new QueueEmptyException('QueueEmpty');
        }
    }
}
