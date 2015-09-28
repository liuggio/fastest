<?php

namespace Liuggio\Fastest;

use Liuggio\Fastest\Event\ChannelIsWaitingEvent;
use Liuggio\Fastest\Event\EmptiedQueueEvent;
use Liuggio\Fastest\Event\EventsName;
use Liuggio\Fastest\Event\FrozenQueueEvent;
use Liuggio\Fastest\Event\LoopCompletedEvent;
use Liuggio\Fastest\Event\LoopStartedEvent;
use Liuggio\Fastest\Event\ProcessCompletedEvent;
use Liuggio\Fastest\Event\ProcessGeneratedBufferEvent;
use Liuggio\Fastest\Event\ProcessStartedEvent;
use Liuggio\Fastest\Process\Process;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class Supervisor implements EventSubscriberInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var int
     */
    private $channelsNumber;

    /**
     * @var bool
     */
    private $queueIsEmpty = false;

    /**
     * @var bool
     */
    private $queueIsFrozen = false;

    /**
     * @var Channels
     */
    private $channels;

    /**
     * @var int
     */
    private $exitCode = 0;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param int                      $channelsNumber
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, $channelsNumber)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->channelsNumber = (int) $channelsNumber;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            EventsName::QUEUE_IS_FROZEN => array('onFrozenQueue', 100),
            EventsName::QUEUE_IS_EMPTY => array('onQueueEmptied', 100),
            EventsName::PROCESS_STARTED => array('onProcessStarted', 100),
            EventsName::PROCESS_COMPLETED => array('onProcessCompleted', 100),
        );
    }

    /**
     * @param FrozenQueueEvent $event
     */
    public function onFrozenQueue(FrozenQueueEvent $event)
    {
        $this->queueIsFrozen = true;
    }

    /**
     * @param EmptiedQueueEvent $event
     */
    public function onQueueEmptied(EmptiedQueueEvent $event)
    {
        $this->queueIsEmpty = true;
    }

    /**
     * @param ProcessStartedEvent $event
     */
    public function onProcessStarted(ProcessStartedEvent $event)
    {
        $channel = $event->getProcess()->getChannel();
        $this->channels->assignAProcess($channel, $event->getProcess());
    }

    /**
     * @param ProcessCompletedEvent $event
     */
    public function onProcessCompleted(ProcessCompletedEvent $event)
    {
        $channel = $event->getProcess()->getChannel();
        $exitCode = $event->getProcess()->getExitCode();
        if (0 !== $exitCode && null !== $exitCode) {
            $this->exitCode = $exitCode;
        }

        $this->channels->setEmpty($channel);
        $this->eventDispatcher->dispatch(EventsName::CHANNEL_IS_WAITING, new ChannelIsWaitingEvent($channel));
    }

    /**
     * @return int
     */
    public function loop()
    {
        $this->channels = Channels::createWaiting($this->channelsNumber);
        $stopWatch = new Stopwatch();
        $stopWatch->start('loop');
        $this->eventDispatcher->dispatch(EventsName::LOOP_STARTED, new LoopStartedEvent($this->channelsNumber));
        $this->notifyWaitingChannel($this->channels->getWaitingChannels());
        while (!($this->queueIsFrozen && $this->queueIsEmpty && count($this->channels->getAssignedChannels()) < 1)) {
            $this->checkTerminatedProcessOnChannels($this->channels->getAssignedChannels());
            usleep(200);
        }
        $stopWatchEvent = $stopWatch->stop('loop');
        $this->eventDispatcher->dispatch(EventsName::LOOP_COMPLETED, new LoopCompletedEvent($stopWatchEvent, $this->exitCode));

        return $this->exitCode;
    }

    /**
     * @param Channel[] $waitingChannels
     */
    private function notifyWaitingChannel($waitingChannels)
    {
        foreach ($waitingChannels as $channel) {
            $this->eventDispatcher->dispatch(
                EventsName::CHANNEL_IS_WAITING,
                new ChannelIsWaitingEvent($channel)
            );
        }
    }

    /**
     * @param Channel[] $assignedChannels
     */
    private function checkTerminatedProcessOnChannels($assignedChannels)
    {
        foreach ($assignedChannels as $channel) {
            /** @var Process $process */
            $process = $channel->getProcess();

            $this->eventDispatcher->dispatch(
                EventsName::PROCESS_GENERATED_BUFFER,
                new ProcessGeneratedBufferEvent($process)
            );

            if ($process->isTerminated()) {
                $this->eventDispatcher->dispatch(EventsName::PROCESS_COMPLETED, new ProcessCompletedEvent($process));
            }
        }
    }
}
