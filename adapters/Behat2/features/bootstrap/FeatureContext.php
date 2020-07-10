<?php

use Behat\Behat\Context\BehatContext;
use Behat\Gherkin\Node\PyStringNode;

class FeatureContext extends BehatContext
{
    private $lastBehatStdOut;

    /**
     * Initializes context.
     *
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->useContext('hooks', new Hooks());
    }

    /**
     * Runs behat command with provided parameters.
     *
     * Taken from Behat's features/bootstrap/Hooks/FeatureContext::iRunBehat() with some modifications
     * Copyright (c) 2011-2013 Konstantin Kudryashov <ever.zet@gmail.com>
     *
     * @When /^I run "behat(?: ([^"]*))?"$/
     *
     * @param string $argumentsString
     */
    public function iRunBehat($argumentsString = '')
    {
        $argumentsString = strtr($argumentsString, ['\'' => '"']);

        if ('/' === DIRECTORY_SEPARATOR) {
            $argumentsString .= ' 2>&1';
        }

        exec($command = sprintf(
            '%s %s %s --no-time',
            BEHAT_PHP_BIN_PATH,
            escapeshellarg(BEHAT_BIN_PATH),
            $argumentsString
        ), $output, $return);

        $this->lastBehatStdOut = trim(implode("\n", $output));
    }

    /**
     * @Then /^the console output should have lines ending in:$/
     */
    public function theConsoleOutputShouldHaveLinesEndingIn(PyStringNode $string)
    {
        $stdOutLines = explode(PHP_EOL, $this->lastBehatStdOut);
        $expectedLines = $string->getLines();
        \PHPUnit\Framework\Assert::assertCount(count($expectedLines), $stdOutLines);

        foreach ($stdOutLines as $idx => $stdOutLine) {
            $suffix = isset($expectedLines[$idx]) ? $expectedLines[$idx] : '(NONE)';
            $constraint = \PHPUnit\Framework\Assert::stringEndsWith($suffix);
            $constraint->evaluate($stdOutLine);
        }
    }

    /**
     * @Then /^the console output should contain "([^"]*)"$/
     */
    public function theConsoleOutputShouldContain($string)
    {
        \PHPUnit\Framework\Assert::assertStringContainsString($string, $this->lastBehatStdOut);
    }

    /**
     * @Given /^the behat\'s FeatureListExtension is enabled$/
     */
    public function theBehatSFeaturelistextensionIsEnabled()
    {
        copy(__DIR__.'/../testResources/behat.yml.resource', 'behat.yml');
    }

    /**
     * @Given /^I have some behat feature files$/
     */
    public function iHaveSomeBehatFeatureFiles()
    {
        $files = [
            'features/bootstrap/FeatureContext.php',
            'features/firstfeature.feature',
            'features/secondfeature.feature',
        ];

        foreach ($files as $file) {
            copy(__DIR__."/../testResources/$file.resource", $file);
        }
    }
}
