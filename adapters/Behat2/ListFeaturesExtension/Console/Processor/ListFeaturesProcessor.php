<?php

namespace Liuggio\Fastest\Behat2\ListFeaturesExtension\Console\Processor;

use Behat\Gherkin\Node\OutlineNode;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Behat\Behat\Console\Processor\Processor as BaseProcessor;

/**
 * Extends Behat command line options to provide options that lists the individual feature files / scenarios that
 * would be executed.
 */
class ListFeaturesProcessor extends BaseProcessor
{
    private $container;

    /**
     * Constructs processor.
     *
     * @param ContainerInterface $container Container instance
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Configures command to be able to process it later.
     *
     * @param Command $command
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
     * Processes data from container and console input.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \RuntimeException
     */
    public function process(InputInterface $input, OutputInterface $output)
    {
        $listFeatures = $input->getOption('list-features');
        $listScenarios = $input->getOption('list-scenarios');

        if (!$listFeatures && !$listScenarios) {
            return;
        }

        $results = [];
        $featurePaths = $this->container->get('behat.console.command')->getFeaturesPaths();
        foreach ($featurePaths as $featurePath) {
            if ($listFeatures) {
                $results = array_merge($results, $this->_getFeatureFiles($featurePath));
            } else {
                $results = array_merge($results, $this->_getScenarios($featurePath));
            }
        }

        $output->writeln(implode(PHP_EOL, $results));
        exit(0);
    }

    /**
     * Returns an array of absolute feature file paths and scenarios line numbers.
     *
     * The format of the file path:
     *
     *  /absolute/path/somefile.feature:XX
     *
     * @param string $featurePath
     *
     * @return array of strings
     */
    private function _getScenarios($featurePath)
    {
        $scenarios = [];
        /* @var $featureNodes \Behat\Gherkin\Node\FeatureNode[] */
        $featureNodes = $this->container->get('gherkin')->load($featurePath);
        foreach ($featureNodes as $featureNode) {
            /* @var $scenario \Behat\Gherkin\Node\ScenarioNode */
            foreach ($featureNode->getScenarios() as $scenario) {
                $file = $scenario->getFile();
                $lines = [$scenario->getLine()];

                if ($scenario instanceof OutlineNode) {
                    $lines = $scenario->getExamples()->getRowLines();
                    array_shift($lines);
                }

                foreach ($lines as $line) {
                    $scenarios[] = "$file:$line";
                }
            }
        }

        return $scenarios;
    }

    /**
     * Returns an array with the absolute paths of the feature files expanded from the $featurePath.
     *
     * @param string $featurePath
     *
     * @return array of strings
     */
    private function _getFeatureFiles($featurePath)
    {
        $featureFiles = [];
        /* @var $featureNodes \Behat\Gherkin\Node\FeatureNode[] */
        $featureNodes = $this->container->get('gherkin')->load($featurePath);
        foreach ($featureNodes as $featureNode) {
            $featureFiles[] = $featureNode->getFile();
        }

        return $featureFiles;
    }
}
