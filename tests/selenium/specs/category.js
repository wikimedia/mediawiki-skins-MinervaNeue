const { iClickOnTheCategoryButton,
		iShouldSeeTheCategoriesOverlay,
		iShouldSeeAListOfCategories
	} = require( '../features/step_definitions/category_steps' ),
	{
		iAmInAWikiThatHasCategories
	} = require( '../features/step_definitions/create_page_api_steps' ),
	{
		iAmUsingTheMobileSite,
		iAmOnPage, iAmInBetaMode
	} = require( '../features/step_definitions/common_steps' );

// Feature: Categories
describe( 'Categories', function () {
	// Scenario: I can view categories
	it( 'I can view categories', function () {
		const title = 'Selenium categories test page';
		// Given I am in a wiki that has categories
		iAmInAWikiThatHasCategories( title );

		// And I am using the mobile site
		iAmUsingTheMobileSite();

		// And I am in beta mode
		iAmInBetaMode();

		// And I am on the "Selenium categories test page" page
		iAmOnPage( title );

		// When I click on the category button
		iClickOnTheCategoryButton();

		// Then I should see the categories overlay
		iShouldSeeTheCategoriesOverlay();

		iShouldSeeAListOfCategories();
	} );
} );
