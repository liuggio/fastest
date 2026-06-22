<?php

namespace Liuggio\Fastest\UI;

use Liuggio\Fastest\Process\Processes;
use Liuggio\Fastest\Process\Report;
use Liuggio\Fastest\Queue\QueueInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

class DotProgressRendererTest extends TestCase
{
    /**
     * @test
     */
    public function shouldRenderADotForSuccessfulReportsAndAnFForFailedReports(): void
    {
        $output = new BufferedOutput(BufferedOutput::VERBOSITY_NORMAL, false);
        $renderer = new DotProgressRenderer(3, false, $output);
        $processes = $this->createProcesses([
            $this->createReport(true),
            $this->createReport(true),
            $this->createReport(false),
        ], 1, [], false);

        $renderer->renderHeader($this->createQueue());
        $renderer->renderBody($this->createQueue(), $processes);
        $renderer->renderFooter($this->createQueue(), $processes);

        $renderedOutput = $output->fetch();
        $this->assertStringContainsString('..F 3/3', $renderedOutput);
        $this->assertStringContainsString('1 failures.', $renderedOutput);
        $this->assertStringContainsString('ehm broken tests', $renderedOutput);
    }

    /**
     * @test
     */
    public function shouldRenderProgressCountEveryConfiguredNumberOfDots(): void
    {
        $output = new BufferedOutput(BufferedOutput::VERBOSITY_NORMAL, false);
        $renderer = new DotProgressRenderer(3, false, $output, 2);
        $processes = $this->createProcesses([
            $this->createReport(true),
            $this->createReport(true),
            $this->createReport(true),
        ]);

        $renderer->renderHeader($this->createQueue());
        $renderer->renderFooter($this->createQueue(), $processes);

        $renderedOutput = $output->fetch();
        $this->assertStringContainsString('.. 2/3', $renderedOutput);
        $this->assertStringContainsString('. 3/3', $renderedOutput);
    }

    /**
     * @test
     */
    public function shouldRenderErrorSummaryWhenEnabled(): void
    {
        $output = new BufferedOutput(BufferedOutput::VERBOSITY_NORMAL, false);
        $renderer = new DotProgressRenderer(1, true, $output);
        $processes = $this->createProcesses(
            [$this->createReport(false)],
            1,
            ['suite' => "Failed step: And I do a thing\n--- Failed scenarios:\npath/to/my.feature:123\n"],
            false
        );

        $renderer->renderFooter($this->createQueue(), $processes);

        $renderedOutput = $output->fetch();
        $this->assertStringContainsString('Failed step: And I do a thing', $renderedOutput);
        $this->assertStringContainsString('path/to/my.feature:123', $renderedOutput);
    }

    /**
     * @param Report[] $reports
     * @param array<string, string> $errorOutput
     */
    private function createProcesses(
        array $reports,
        int $errorCount = 0,
        array $errorOutput = [],
        bool $successful = true
    ): Processes {
        $processes = $this->getMockBuilder(Processes::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $processes
            ->method('getReport')
            ->willReturn($reports)
        ;
        $processes
            ->method('countErrors')
            ->willReturn($errorCount)
        ;
        $processes
            ->method('getErrorOutput')
            ->willReturn($errorOutput)
        ;
        $processes
            ->method('isSuccessful')
            ->willReturn($successful)
        ;

        return $processes;
    }

    private function createReport(bool $successful): Report
    {
        return new Report('suite', $successful, 0.1, 1, null, false);
    }

    private function createQueue(): QueueInterface
    {
        return $this->createMock(QueueInterface::class);
    }
}
