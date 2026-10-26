<?php

namespace Liuggio\Fastest\Queue;

/**
 * @internal
 */
trait TestsQueueAddTrait
{
    /**
     * @param string|TestSuite $value
     */
    public function add($value): bool
    {
        if (!$value instanceof TestSuite) {
            $value = new TestSuite($value);
        }

        parent::add($value);

        trigger_deprecation(
            'liuggio/fastest',
            '1.10',
            'The return value of Liuggio\Fastest\Queue\TestsQueue:add will change to void in v2 of the package'
        );

        return true;
    }
}
