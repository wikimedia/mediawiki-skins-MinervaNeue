@chrome @firefox @login @test2.m.wikipedia.org @vagrant
Feature:  User:<username>

  Background:
    Given I am logged into the mobile website
    And I visit my user page

  @en.m.wikipedia.beta.wmflabs.org
  Scenario: Check components in user page
    Then I should be on my user page
    And there should be a link to my talk page
    And there should be a link to my contributions
    And there should be a link to my uploads
