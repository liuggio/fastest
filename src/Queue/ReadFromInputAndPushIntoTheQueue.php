<?php
namespace Liuggio\Fastest\Queue;

use Liuggio\Fastest\Exception\QueueEmptyException;

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

        $queue = $this->queueFactory->create();

        $queue->push($testsQueue);

        return $queue;
    }

    private function assertTestsQueueIsNotEmpty(TestsQueue $testsQueue)
    {
        if ($testsQueue->isEmpty()) {
            throw new \Exception('Empty input try piping some files.');
        }
    }
}
