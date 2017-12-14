<?php

namespace Liuggio\Fastest\Behat\ListFeaturesExtension\Console\Processor;

use Behat\Gherkin\Node\OutlineNode;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Behat\Testwork\Cli\Controller;
use Behat\Testwork\Specification\Locator\SpecificationLocator;
use Behat\Testwork\Suite\SuiteRepository;

/**
 * Extends Behat command line options to provide options that lists the individual feature files / scenarios that
 * would be executed.
 */
class ListFeaturesProcessor implements Controller
{
    /**
     * @var SpecificationLocator
     */
    private $locator;

    /**
     * @var SuiteRepository
     */
    private $registry;

    /**
     * Constructs processor.
     *
     * @param SpecificationLocator $locator
     * @param SuiteRepository      $registry
     */
    public function __construct(SpecificationLocator $locator, SuiteRepository $registry)
    {
        $this->locator = $locator;
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(Command $command)
    {
        $command->addOption(
            'list-features',
            null,
            InputOption::VALUE_NONE,
            'Output a list of feature files to be executed by Behat'
        );
        $command->addOption(
            'list-scenarios',
            null,
            InputOption::VALUE_NONE,
            'Output a list of individual scenarios to be executed by Behat'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $listFeatures = $input->getOption('list-features');
        $listScenarios = $input->getOption('list-scenarios');

        if (!$listFeatures && !$listScenarios) {
            return;
        }

        $results = [];
        if ($listFeatures) {
            $results = array_merge($results, $this->getFeatureFiles());
        } else {
            $results = array_merge($results, $this->getScenarios());
        }

        $output->writeln(implode(PHP_EOL, $results));
        exit(0);
    }

    /**
     * {@inheritdoc}
     */
    public function getScenarios()
    {
        $scenarios = [];

        foreach ($this->registry->getSuites() as $suite) {
            foreach ($this->locator->locateSpecifications($suite, '') as $feature) {
                foreach ($feature->getScenarios() as $key => $scenario) {
                    $file = $feature->getFile();
                    $lines = [$scenario->getLine()];

                    if ($scenario instanceof OutlineNode) {
                        $lines = $scenario->getExampleTable()->getLines();
                        array_shift($lines);
                    }

                    foreach ($lines as $line) {
                        $scenarios[] = "$file:$line";
                    }
                }
            }
        }

        return $scenarios;
    }

    /**
     * {@inheritdoc}
     */
    private function getFeatureFiles()
    {
        $featureFiles = [];
        foreach ($this->registry->getSuites() as $suite) {
            foreach ($this->locator->locateSpecifications($suite, '') as $feature) {
                $featureFiles[] = $feature->getFile();
            }
        }

        return $featureFiles;
    }
}
