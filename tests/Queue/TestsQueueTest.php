<?php

namespace Liuggio\Fastest\Queue;

use Composer\InstalledVersions;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class TestsQueueTest extends TestCase
{
    public function testConstructorWrapsPathsInTestSuites(): void
    {
        $queue = new TestsQueue([' first.php ', 'second.php']);

        $this->assertCount(2, $queue);
        $this->assertInstanceOf(TestSuite::class, $queue->first());
        $this->assertSame('first.php', (string) $queue->first());
        $this->assertSame('second.php', (string) $queue->last());
    }

    public function testAddWrapsPathsAndPreservesExistingTestSuites(): void
    {
        $queue = new TestsQueue();
        $suite = new TestSuite('existing.php');

        $queue->add(' new.php ');
        $queue->add($suite);

        $this->assertCount(2, $queue);
        $this->assertInstanceOf(TestSuite::class, $queue->first());
        $this->assertSame('new.php', (string) $queue->first());
        $this->assertSame($suite, $queue->last());
    }

    public function testConstructorAcceptsExistingTestSuites(): void
    {
        $suite = new TestSuite('existing.php');
        $queue = new TestsQueue([$suite]);

        $this->assertSame($suite, $queue->first());
    }

    public function testAddPreservesTheLegacyReturnValueAndDeprecation(): void
    {
        $queue = new TestsQueue();
        $add = new \ReflectionMethod(TestsQueue::class, 'add');
        $deprecations = [];
        set_error_handler(static function (int $level, string $message) use (&$deprecations): bool {
            if (E_USER_DEPRECATED !== $level) {
                return false;
            }

            $deprecations[] = $message;

            return true;
        });

        try {
            $returnValue = $add->invoke($queue, 'test.php');
        } finally {
            restore_error_handler();
        }

        $legacyCollections = version_compare(InstalledVersions::getVersion('doctrine/collections') ?? '', '3.0.0', '<');
        $this->assertSame($legacyCollections ? true : null, $returnValue);
        $this->assertSame($legacyCollections ? 'bool' : 'void', (string) $add->getReturnType());
        $this->assertSame(ArrayCollection::class, get_parent_class($queue));
        $this->assertSame('test.php', (string) $queue->first());

        if ($legacyCollections) {
            $this->assertCount(1, $deprecations);
            $this->assertStringContainsString('The return value of Liuggio\\Fastest\\Queue\\TestsQueue:add will change to void in v2 of the package', $deprecations[0]);
        } else {
            $this->assertSame([], $deprecations);
        }
    }

    public function testRandomizeKeepsAllTestSuitesAndTheOriginalOrder(): void
    {
        $first = new TestSuite('first.php');
        $second = new TestSuite('second.php');
        $queue = new TestsQueue([$first, $second]);

        $randomized = $queue->randomize();

        $this->assertNotSame($queue, $randomized);
        $this->assertSame([$first, $second], $queue->toArray());
        $this->assertFalse($queue->hasBeenRandomized());
        $this->assertTrue($randomized->hasBeenRandomized());
        $this->assertCount(2, $randomized);
        $this->assertTrue($randomized->contains($first));
        $this->assertTrue($randomized->contains($second));
    }

    public function testFilterCreatesAQueueWithTheSameTestSuiteInstances(): void
    {
        $first = new TestSuite('first.php');
        $second = new TestSuite('second.php');
        $queue = new TestsQueue([$first, $second]);

        $filtered = $queue->filter(static function (TestSuite $suite): bool {
            return 'second.php' === $suite->getTestPath();
        });

        $this->assertInstanceOf(TestsQueue::class, $filtered);
        $this->assertSame([$second], $filtered->toArray());
        $this->assertSame([$first, $second], $queue->toArray());
    }

    public function testAddRejectsAnEmptyTestPath(): void
    {
        $queue = new TestsQueue();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('empty value');

        $queue->add(' ');
    }
}
