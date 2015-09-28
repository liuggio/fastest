<?php

namespace Liuggio\Fastest\Producer;

use Liuggio\Fastest\Exception\StdInMustBeAValidResourceException;
use Liuggio\Fastest\InputLine;
use Liuggio\Fastest\Queue\QueueInterface;

class StdInProducer implements ProducerInterface
{
    /**
     * @var string
     */
    private $stdIn;

    /**
     * @var resource
     */
    private $resource;

    /**
     * @param string $stdIn
     */
    public function __construct($stdIn = 'php://stdin')
    {
        $this->stdIn = (string) $stdIn;
    }

    /**
     * {@inheritdoc}
     */
    public function produce(QueueInterface $queue)
    {
        $this->resource = @fopen($this->stdIn, 'r');
        $this->assertResourceIsValid();

        while (false !== ($line = fgets($this->resource))) {
            $this->addLineIfNotEmpty($queue, $line);
        }
        $queue->freeze();
    }

    public function __destruct()
    {
        if (null !== $this->resource) {
            @fclose($this->resource);
        }
    }

    /**
     * @param QueueInterface $queue
     * @param string         $line
     */
    private function addLineIfNotEmpty(QueueInterface $queue, $line)
    {
        $line = trim($line);
        if (!empty($line)) {
            $queue->enqueue(InputLine::fromString($line));
        }
    }

    /**
     * @throws StdInMustBeAValidResourceException
     */
    private function assertResourceIsValid()
    {
        if (!$this->resource) {
            throw new StdInMustBeAValidResourceException($this->stdIn);
        }
    }
}
