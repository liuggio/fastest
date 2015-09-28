<?php

namespace Liuggio\Fastest\Consumer;

use Liuggio\Fastest\CommandLine;
use Liuggio\Fastest\Event\ChannelIsWaitingEvent;
use Liuggio\Fastest\Event\EventsName;
use Liuggio\Fastest\Event\ProcessStartedEvent;
use Liuggio\Fastest\Process\CreateAndStartAProcess;
use Liuggio\Fastest\Queue\QueueInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ConsumerListener
{
    /**
     * @var int
     */
    private $processCounter;

    /**
     * @var QueueInterface
     */
    private $queue;

    /**
     * @var CreateAndStartAProcess
     */
    private $createAndStartAProcess;

    /**
     * @var CommandLine
     */
    private $baseCommandLine;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param QueueInterface              $queue
     * @param CommandLine                 $baseCommandLine
     * @param EventDispatcherInterface    $eventDispatcher
     * @param CreateAndStartAProcess|null $createStartAndWaitAProcess
     */
    public function __construct(
        QueueInterface $queue,
        CommandLine $baseCommandLine,
        EventDispatcherInterface $eventDispatcher,
        CreateAndStartAProcess $createStartAndWaitAProcess = null
    ) {
        $this->queue = $queue;
        $this->baseCommandLine = $baseCommandLine;
        $this->eventDispatcher = $eventDispatcher;
        $this->createAndStartAProcess = $createStartAndWaitAProcess ?: new CreateAndStartAProcess();
        $this->processCounter = 0;
    }

    /**
     * @param ChannelIsWaitingEvent $event
     */
    public function onChannelIsWaiting(ChannelIsWaitingEvent $event)
    {
        $channel = $event->getChannel();
        $event->stopPropagation();

        while (null === ($value = $this->queue->dequeue())) {
            if ($this->queue->isFrozen()) {
                return;
            }
            usleep(1000);
        }
        ++$this->processCounter;
        $process = $this->createAndStartAProcess->createAndStartAProcess(
            $channel,
            $this->baseCommandLine,
            $value,
            $this->processCounter
        );
        $this->eventDispatcher->dispatch(EventsName::PROCESS_STARTED, new ProcessStartedEvent($process));
    }
}
