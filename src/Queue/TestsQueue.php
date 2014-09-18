<?php
namespace Liuggio\Fastest\Queue;

use Doctrine\Common\Collections\ArrayCollection;

class TestsQueue extends ArrayCollection
{
    /**
     * Initializes a new ArrayCollection.
     *
     * @param array $elements
     */
    public function __construct(array $elements = array())
    {
        parent::__construct();
        foreach ($elements as $element) {
            $this->add($element);
        }
    }

    /**
     * Create another instance with randomized items.
     *
     * @return Tests
     */
    public function randomize()
    {
        $array = $this->toArray();
        shuffle($array);

        return new self($array);
    }

    /**
     * {@inheritDoc}
     */
    public function add($value)
    {
       if (!($value instanceof TestSuite)) {
           $value = new TestSuite($value);
       }

       return parent::add($value);
    }

    /**
     * {@inheritDoc}
     */
    public function isEmpty()
    {
        return parent::isEmpty();
    }
}
