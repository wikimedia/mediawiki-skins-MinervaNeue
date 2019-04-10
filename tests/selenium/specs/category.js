const { iClickOnTheCategoryButton,
		iShouldSeeTheCategoriesOverlay,
		iShouldSeeAListOfCategories
	} = require( '../features/step_definitions/category_steps' ),
	{
		iAmInAWikiThatHasCategories
	} = require( '../features/step_definitions/create_page_api_steps' ),
	{
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
		// And I am in beta mode
		iAmInBetaMode();

		// And I am on the "Selenium categories test page" page
		iAmOnPage( title );

		// When I click on the category button
		iClickOnTheCategoryButton();

		// Then I should see the categories overlay
		iShouldSeeTheCategoriesOverlay();

		// FIXME: This check is partially skipped as there is no way to lower $wgJobRunRate
		// See: T199939#5095838
		try {
			iShouldSeeAListOfCategories();
		} catch ( e ) {
			// pass.
			// eslint-disable-next-line no-console
			console.warn( 'Unable to check the list of the categories. Is wgJobRunRate set correctly?' );
		}
	} );
} );
