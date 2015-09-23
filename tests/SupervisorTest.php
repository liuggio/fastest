<?php

namespace Liuggio\Fastest;

use Liuggio\Fastest\Event\EventsName;

class SupervisorTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function shouldLoopUntilQueueisEmptiedAndFrozen()
    {
        $ed = $this->getMock('\Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $ed->expects($this->at(0))
            ->method('dispatch')
            ->with($this->equalTo(EventsName::LOOP_STARTED));

        $ed->expects($this->at(1))
            ->method('dispatch')
            ->with($this->equalTo(EventsName::CHANNEL_IS_WAITING));

        $ed->expects($this->at(2))
            ->method('dispatch')
            ->with($this->equalTo(EventsName::CHANNEL_IS_WAITING));

        $ed->expects($this->at(3))
            ->method('dispatch')
            ->with($this->equalTo(EventsName::LOOP_COMPLETED));

        $supervisor = new Supervisor($ed, 2);

        $ev = $this->getMock('\Liuggio\Fastest\Event\FrozenQueueEvent');
        $supervisor->onFrozenQueue($ev);
        $ev2 = $this->getMock('\Liuggio\Fastest\Event\EmptiedQueueEvent');
        $supervisor->onQueueEmptied($ev2);

        $supervisor->loop();
    }
}
