<?php

namespace Liuggio\Fastest\Queue;

class ReadFromInputAndPushIntoTheQueue
{
    /**
     * @var QueueFactoryInterface
     */
    protected $queueFactory;

    /**
     * @var null
     */
    protected $queuePort;

    public function __construct(QueueFactoryInterface $queueFactory)
    {
        $this->queueFactory = $queueFactory;
    }

    public function execute(string $xmlFile = null, bool $preserveOrder = false): QueueInterface
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

    private function assertTestsQueueIsNotEmpty(TestsQueue $testsQueue): void
    {
        if ($testsQueue->isEmpty()) {
            throw new \Exception('Empty input try piping some files.');
        }
    }
}
