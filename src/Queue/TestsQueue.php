<?php

namespace Liuggio\Fastest\Queue;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @extends ArrayCollection<int, TestSuite>
 */
class TestsQueue extends ArrayCollection
{
    /**
     * @var bool
     */
    private $hasBeenRandomized;

    /**
     * Initializes a new ArrayCollection.
     *
     * @param array<int, string> $elements
     * @param bool  $hasBeenRandomized
     */
    public function __construct(array $elements = [], bool $hasBeenRandomized = false)
    {
        parent::__construct();
        foreach ($elements as $element) {
            $this->add($element);
        }
        $this->hasBeenRandomized = $hasBeenRandomized;
    }

    /**
     * Create another instance with randomized items.
     *
     * @return TestsQueue
     */
    public function randomize(): TestsQueue
    {
        $array = $this->toArray();
        shuffle($array);

        return new self($array, true);
    }

    /**
     * @param string|TestSuite $value
     *
     * @return bool
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

    /**
     * {@inheritdoc}
     */
    public function isEmpty(): bool
    {
        return parent::isEmpty();
    }


    public function hasBeenRandomized(): bool
    {
        return $this->hasBeenRandomized;
    }
}
