<?php

namespace Liuggio\Fastest\Command;

use Liuggio\Fastest\Queue\QueueInterface;
use Liuggio\Fastest\UI\DotProgressRenderer;
use Liuggio\Fastest\UI\NoProgressRenderer;
use Liuggio\Fastest\UI\ProgressBarRenderer;
use Liuggio\Fastest\UI\RendererInterface;
use Liuggio\Fastest\UI\VerboseRenderer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

class ParallelCommandTest extends TestCase
{
    /**
     * @test
     */
    public function shouldUseProgressBarRendererByDefault(): void
    {
        $command = new TestableParallelCommand();

        $renderer = $command->renderer(
            $this->createInput($command),
            $this->createOutput(),
            $this->createQueue(),
            false,
            false
        );

        $this->assertInstanceOf(ProgressBarRenderer::class, $renderer);
    }

    /**
     * @test
     */
    public function shouldUseDotProgressRendererWhenDotProgressIsRequested(): void
    {
        $command = new TestableParallelCommand();

        $renderer = $command->renderer(
            $this->createInput($command),
            $this->createOutput(),
            $this->createQueue(),
            false,
            true
        );

        $this->assertInstanceOf(DotProgressRenderer::class, $renderer);
    }

    /**
     * @test
     */
    public function shouldUseNoProgressRendererBeforeDotProgressRenderer(): void
    {
        $command = new TestableParallelCommand();

        $renderer = $command->renderer(
            $this->createInput($command),
            $this->createOutput(),
            $this->createQueue(),
            true,
            true
        );

        $this->assertInstanceOf(NoProgressRenderer::class, $renderer);
    }

    /**
     * @test
     */
    public function shouldUseVerboseRendererForVerboseOutputWhenDotProgressIsNotRequested(): void
    {
        $command = new TestableParallelCommand();

        $renderer = $command->renderer(
            $this->createInput($command),
            $this->createOutput(OutputInterface::VERBOSITY_VERBOSE),
            $this->createQueue(),
            false,
            false
        );

        $this->assertInstanceOf(VerboseRenderer::class, $renderer);
    }

    /**
     * @test
     */
    public function shouldShowRerunErrorSummaryWhenErrorsSummaryIsEnabled(): void
    {
        $command = new TestableParallelCommand();

        $this->assertTrue($command->rerunErrorSummary($this->createInput($command)));
    }

    /**
     * @test
     */
    public function shouldHideRerunErrorSummaryWhenErrorsSummaryIsDisabled(): void
    {
        $command = new TestableParallelCommand();

        $this->assertFalse($command->rerunErrorSummary($this->createInput($command, ['--no-errors-summary' => true])));
    }

    /**
     * @test
     */
    public function shouldShowRerunErrorSummaryWhenExplicitlyRequested(): void
    {
        $command = new TestableParallelCommand();

        $input = $this->createInput($command, [
            '--no-errors-summary' => true,
            '--show-rerun-failed' => true,
        ]);

        $this->assertTrue($command->rerunErrorSummary($input));
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function createInput(TestableParallelCommand $command, array $parameters = []): ArrayInput
    {
        return new ArrayInput($parameters, $command->getDefinition());
    }

    private function createOutput(int $verbosity = OutputInterface::VERBOSITY_NORMAL): BufferedOutput
    {
        return new BufferedOutput($verbosity, false);
    }

    private function createQueue(): QueueInterface
    {
        $queue = $this->createMock(QueueInterface::class);
        $queue
            ->method('count')
            ->willReturn(3)
        ;

        return $queue;
    }
}

class TestableParallelCommand extends ParallelCommand
{
    public function renderer(
        InputInterface $input,
        OutputInterface $output,
        QueueInterface $queue,
        bool $noProgressOption,
        bool $dotProgressOption
    ): RendererInterface {
        return $this->createRenderer($input, $output, $queue, $noProgressOption, $dotProgressOption);
    }

    public function rerunErrorSummary(InputInterface $input): bool
    {
        return $this->hasRerunErrorSummary($input);
    }
}
