<?php

namespace Liuggio\Fastest\Producer;

class StdInProducerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \Liuggio\Fastest\Exception\StdInMustBeAValidResourceException
     */
    public function shouldRaiseAnExceptionIfInputHasError()
    {
        $queue = $this->getMock('\Liuggio\Fastest\Queue\QueueInterface');
        $sut = new StdInProducer('xyz');
        $sut->produce($queue);
    }

    /**
     * @test
     */
    public function shouldFillTheQueueWith3Lines()
    {
        $buffer = <<<EOF
line
line
line
EOF;
        $createTmpFileName = tempnam(sys_get_temp_dir(), 'fastest_test');
        file_put_contents($createTmpFileName, $buffer);

        $queue = $this->getMock('\Liuggio\Fastest\Queue\QueueInterface');
        $queue
            ->expects($this->exactly(3))
            ->method('enqueue');
        $sut = new StdInProducer($createTmpFileName);
        $sut->produce($queue);
    }

    /**
     * @test
     *
     * This test intents to detect problems when reading from stdin, simulating a delayed input into fastest.
     *
     * For example stream_set_blocking(stdin, false) was causing problems because it returned too fast
     * and stdin was read as an empty string from certain programs (i.e. behat --list-scenarios)
     * See issue https://github.com/liuggio/fastest/issues/10.
     */
    public function shouldReadTestQueueFromDelayedStdIn()
    {
        $bootstrapFile = realpath(__DIR__.'/../../vendor/autoload.php');
        $code = '
            require "'.$bootstrapFile.'";
            $queue = new \Liuggio\Fastest\Queue\SplQueue();
            $producer = new \Liuggio\Fastest\Producer\StdInProducer();
            $producer->produce($queue);
            while(null !== ($value = $queue->dequeue())) {
                echo $value . PHP_EOL;
            }
        ';
        $code = escapeshellarg($code);
        $command = 'php -r'.$code;
        $stdInLines = array(
            'Line 1'.PHP_EOL,
            'Line 2'.PHP_EOL,
        );
        $expectedStdOut = implode('', $stdInLines);
        $stdOut = $this->executeCommandWithDelayedStdin($command, $stdInLines);
        $this->assertEquals($expectedStdOut, $stdOut);
    }

    private function executeCommandWithDelayedStdin($command, $stdInLines, $delayMicroseconds = 1000000)
    {
        $descriptors = array(
            0 => array('pipe', 'r'),  // stdin is a pipe that the child will read from
            1 => array('pipe', 'w'),  // stdout is a pipe that the child will write to
            2 => array('pipe', 'w'), // stderr is a pipe that the child will write to
        );
        $pipes = array();
        $process = proc_open($command, $descriptors, $pipes);
        if (!is_resource($process)) {
            throw new \RuntimeException("Failed to run command '$command'");
        }
        // $pipes now looks like this:
        // 0 => writable handle connected to child stdin
        // 1 => readable handle connected to child stdout
        // 2 => readable handle connected to child stderr
        foreach ($stdInLines as $stdInLine) {
            usleep($delayMicroseconds);
            fwrite($pipes[0], $stdInLine);
        }
        fclose($pipes[0]);
        $stdOut = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stdErr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        if ($stdErr) {
            throw new \RuntimeException("Error executing $command: $stdErr");
        }
        // It is important that to close any pipes before calling
        // proc_close in order to avoid a deadlock
        proc_close($process);

        return $stdOut;
    }
}
