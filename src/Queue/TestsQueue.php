<?php
namespace Liuggio\Fastest\Queue;

use Doctrine\Common\Collections\ArrayCollection;

class TestsQueue extends ArrayCollection
{
    private $hasBeenRandomized;

    /**
     * Initializes a new ArrayCollection.
     *
     * @param array $elements
     * @param bool  $hasBeenRandomized
     */
    public function __construct(array $elements = array(), $hasBeenRandomized = false)
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
     * @return Tests
     */
    public function randomize()
    {
        $array = $this->toArray();
        shuffle($array);

        return new self($array, true);
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


    /**
     * @return boolean
     */
    public function hasBeenRandomized()
    {
        return $this->hasBeenRandomized;
    }
}
