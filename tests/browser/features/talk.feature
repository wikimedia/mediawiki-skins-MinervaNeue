@chrome @en.m.wikipedia.beta.wmflabs.org @firefox @test2.m.wikipedia.org @vagrant
Feature: Talk

  Background:
    Given I am using the mobile site

  @login
  Scenario: Talk on a page that does exist
    Given the page "Talk:Selenium talk test" exists
      And I am logged into the mobile website
      And the page "Selenium talk test" exists
    When I click the talk button
    Then I should see the talk overlay

  @login
  Scenario: Talk on a page that doesn't exist (bug 64268)
    Given I am logged into the mobile website
      And I am on a page that does not exist
    When I click the talk button
    Then I should see the talk overlay

  @smoke @login
  Scenario: Add discussion for talk page possible as logged in user
    Given the page "Talk:Selenium talk test" exists
      And I am logged into the mobile website
      And the page "Selenium talk test" exists
    When I click the talk button
    Then there should be an add discussion button

  @smoke @login
  Scenario: Add topic button shows on talk pages for logged in users
    Given the page "Talk:Selenium talk test" exists
      And I am logged into the mobile website
      And I am on the "Talk:Selenium UI test" page
    When I click the talk button
    Then there should be a save discussion button

  Scenario: A newly created topic appears in the list of topics immediately
    Given the page "Talk:Selenium talk test" exists
      And I am logged into the mobile website
      And the page "Selenium talk test" exists
    When I click the talk button
    And I see the talk overlay
	  And no topic is present
	  And I add a topic called "New topic"
    Then I should see the topic called "New topic" in the list of topics

  Scenario: Add discussion on talk page not possible as logged out user
    Given the page "Talk:Selenium talk test" exists
      And the page "Selenium talk test" exists
    Then there should be no talk button
