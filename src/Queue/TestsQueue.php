<?php

namespace Liuggio\Fastest\Queue;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @extends ArrayCollection<int, TestSuite>
 */
class TestsQueue extends ArrayCollection
{
    use TestsQueueAddTrait;

    /**
     * @var bool
     */
    private $hasBeenRandomized;

    /**
     * Initializes a new ArrayCollection.
     *
     * @param array<int, string|TestSuite> $elements
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
