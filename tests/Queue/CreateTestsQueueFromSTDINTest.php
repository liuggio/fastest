<?php

namespace Liuggio\Fastest\Queue;

use PHPUnit\Framework\TestCase;

class CreateTestsQueueFromSTDINTest extends TestCase
{
    /**
     * @test
     *
     * This test intents to detect problems when reading from stdin, simulating a delayed input into fastest.
     *
     * For example stream_set_blocking(stdin, false) was causing problems because it returned too fast
     * and stdin was read as an empty string from certain programs (i.e. behat --list-scenarios)
     * See issue https://github.com/liuggio/fastest/issues/10.
     */
    public function shouldReadTestQueueFromDelayedStdin(): void
    {
        $bootstrapFile = realpath(__DIR__.'/../../vendor/autoload.php');

        $code = '
            require "'.$bootstrapFile.'";
            $queue = new \Liuggio\Fastest\Queue\CreateTestsQueueFromSTDIN();
            $tests = $queue->execute();

            foreach($tests as $test) {
                echo $test . PHP_EOL;
            }
        ';

        $code = escapeshellarg($code);
        $command = 'php -r'.$code;

        $stdinLines = [
           'Line 1'.PHP_EOL,
           'Line 2'.PHP_EOL,
        ];

        $expectedStdout = implode('', $stdinLines);

        $stdOut = $this->executeCommandWithDelayedStdin($command, $stdinLines);

        $this->assertEquals($expectedStdout, $stdOut);
    }

    /**
     * @param string $command
     * @param array<string> $stdinLines
     *
     * @return string
     */
    private function executeCommandWithDelayedStdin(string $command, array $stdinLines): string
    {
        $descriptors = [
            0 => ['pipe', 'r'],  // stdin is a pipe that the child will read from
            1 => ['pipe', 'w'],  // stdout is a pipe that the child will write to
            2 => ['pipe', 'w'], // stderr is a pipe that the child will write to
        ];

        $pipes = [];
        $process = proc_open($command, $descriptors, $pipes);

        if (!is_resource($process)) {
            throw new \RuntimeException("Failed to run command '$command'");
        }

        // $pipes now looks like this:
        // 0 => writable handle connected to child stdin
        // 1 => readable handle connected to child stdout
        // 2 => readable handle connected to child stderr

        foreach ($stdinLines as $stdinLine) {
            usleep(1000000);
            fwrite($pipes[0], $stdinLine);
        }
        fclose($pipes[0]);

        $stdOut = stream_get_contents($pipes[1]);
        if (!$stdOut) {
            throw new \RuntimeException("Can't read from stdout");
        }

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
