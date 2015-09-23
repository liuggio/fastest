<?php

namespace Liuggio\Fastest\Process;

use Liuggio\Fastest\CommandLine;
use Liuggio\Fastest\InputLine;

class ApplyCommandLineTemplateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldCreateCommandLineCreatingACorrectCommandLine()
    {
        $template = new ApplyCommandLineTemplate();

        $processEnv = $this->createProcessEnvMock(1, 5, InputLine::fromString('fileA'));

        $commandLine = $template->createCommandLine(new CommandLine('execute {p} {}'), $processEnv);

        $this->assertInstanceOf('\Liuggio\Fastest\CommandLine', $commandLine);
        $this->assertEquals('execute 1 fileA', (string) $commandLine);
    }

    /**
     * @test
     */
    public function shouldCreateCommandLineCreatingACorrectCommandLineFromACustomCommandLine()
    {
        $template = new ApplyCommandLineTemplate();
        $processEnv = $this->createProcessEnvMock(1, 5, InputLine::fromString('fileA'));

        $commandLine = $template->createCommandLine(new CommandLine('{p} {} {inc}'), $processEnv);

        $this->assertInstanceOf('\Liuggio\Fastest\CommandLine', $commandLine);
        $this->assertEquals('1 fileA 5', (string) $commandLine);
    }

    private function createProcessEnvMock($channel, $incremental, $argument)
    {
        $processEnv = $this->getMockBuilder('\Liuggio\Fastest\Process\ProcessEnvironment')
            ->disableOriginalConstructor()
            ->getMock();
        $processEnv->expects($this->once())
            ->method('getChannelId')
            ->will($this->returnValue($channel));
        $processEnv->expects($this->once())
            ->method('getIncrementalNumber')
            ->will($this->returnValue($incremental));
        $processEnv->expects($this->once())
            ->method('getInputLine')
            ->will($this->returnValue($argument));

        return $processEnv;
    }
}
