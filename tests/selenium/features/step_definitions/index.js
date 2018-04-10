const { defineSupportCode } = require( 'cucumber' ),
	{ iClickOnTheCategoryButton,
		iShouldSeeTheCategoriesOverlay, iShouldSeeAListOfCategories
	} = require( './category_steps' ),
	{ iAmInAWikiThatHasCategories } = require( './create_page_api_steps' ),
	{
		iAmUsingTheMobileSite,
		iAmLoggedIntoTheMobileWebsite,
		iAmOnPage, iAmInBetaMode
	} = require( './common_steps' ),
	{
		iClickOnTheHistoryLinkInTheLastModifiedBar
	} = require( './history_steps' );

defineSupportCode( function ( { Then, When, Given } ) {

	// common steps
	Given( /^I am using the mobile site$/, iAmUsingTheMobileSite );

	Given( /^I am in beta mode$/, iAmInBetaMode );

	Given( /^I am on the "(.+)" page$/, iAmOnPage );

	Given( /^I am logged into the mobile website$/, iAmLoggedIntoTheMobileWebsite );

	// Page steps
	Given( /^I am in a wiki that has categories$/, () => {
		iAmInAWikiThatHasCategories( 'Selenium categories test page' );
	} );

	// history steps
	When( /^I click on the history link in the last modified bar$/,
		iClickOnTheHistoryLinkInTheLastModifiedBar );

	// Category steps
	When( /^I click on the category button$/, iClickOnTheCategoryButton );

	Then( /^I should see the categories overlay$/, iShouldSeeTheCategoriesOverlay );

	Then( /^I should see a list of categories$/, iShouldSeeAListOfCategories );
} );
