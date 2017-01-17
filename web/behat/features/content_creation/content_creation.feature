Feature: Automatic content creation
When I log into the website
I should be able to call the ContentCreation service to create content

  @api
  Scenario: Create a subdivision
    Given I connect to the test database
    Given I have a clean database
    Given I have test data
    Given I am logged in as a user with the "editor" role
    Given I have called updateSubdivision with subdivisionId 10 subdivisionName "SuperDivision" type "Villagrund" postal code 8989 city name "FiskeByen"
    Then I should be able to find the "cities" term with name "Fiskebyen"
