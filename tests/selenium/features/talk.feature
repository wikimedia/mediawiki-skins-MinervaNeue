@chrome @en.m.wikipedia.beta.wmflabs.org @firefox @test2.m.wikipedia.org @vagrant
Feature: Talk

  Background:
    Given I am using the mobile site

  Scenario: Talk button not visible as logged out user
    Given the page "Selenium talk test" exists
      And I am on the "Selenium talk test" page
    Then there should be no talk button

  @login
  Scenario: Talk button visible as logged in user
    Given the page "Selenium talk test" exists
      And I am logged into the mobile website
      And I am on the "Selenium talk test" page
    Then there should be a talk button

  @login
  Scenario: Talk on a page that doesn't exist (bug 64268)
    Given I am logged into the mobile website
      And I am on a page that does not exist
    Then there should be a talk button

  @smoke @login
  Scenario: Add discussion button shows on talk pages for logged in users
    Given the page "Talk:Selenium talk test" exists
      And I am logged into the mobile website
      And I am on the "Talk:Selenium talk test" page
    Then there should be an add discussion button

  @smoke @login
  Scenario: Add discussion for talk page possible as logged in user
    Given the page "Talk:Selenium talk test" exists
      And I am logged into the mobile website
      And I am on the "Talk:Selenium talk test" page
    When I click the add talk button
    Then there should be a save discussion button

  Scenario: A newly created topic appears in the list of topics
    Given I am logged into the mobile website
      And I am on a talk page with no talk topics
      And no topic is present
    When I click the talk button
      And I see the talk overlay
      And I add a topic called "New topic"
    Then I should see the topic called "New topic" in the list of topics
