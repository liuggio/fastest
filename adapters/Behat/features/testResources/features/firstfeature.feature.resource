Feature: First feature

  Scenario: Scenario in line 3
    Given Step one
    When Step two
    Then Step three

  Scenario: Scenario in line 8
    Given Step four
    Then Step one

  Scenario Outline: Scenario in line 12
    Given Step <first>
    Then Step <second>

    Examples:
      | first | second |
      | A     | B      |
      | C     | D      |
