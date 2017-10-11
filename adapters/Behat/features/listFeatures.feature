Feature: List Features Behat extension
  In order to input fastest with Behat scenarios
  As a tester
  I need to be able to show Behat scenarios as a list and pipe them into fastest

Background:
  Given the behat's FeatureListExtension is enabled

Scenario: Behat shows the list feature commands in its help
  Given I run "behat -h"
  Then the console output should contain "--list-features"
  And the console output should contain "--list-scenarios"

Scenario: The --list-features command outputs the names of the feature files
  Given I have some behat feature files
  When I run "behat --list-features features/"
  Then the console output should have lines ending in:
  """
  features/firstfeature.feature
  features/secondfeature.feature
  """

Scenario: The --list-scenarios command outputs each feature with the line number of each scenario
  Given I have some behat feature files
  When I run "behat --list-scenarios features/"
  Then the console output should have lines ending in:
  """
  features/firstfeature.feature:3
  features/firstfeature.feature:8
  features/firstfeature.feature:18
  features/firstfeature.feature:19
  features/secondfeature.feature:3
  features/secondfeature.feature:7
  features/secondfeature.feature:12
  """
