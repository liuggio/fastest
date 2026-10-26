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
    public function add(mixed $value): void
    {
        if (!$value instanceof TestSuite) {
            $value = new TestSuite($value);
        }

        parent::add($value);
    }
}
