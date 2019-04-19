const { defineSupportCode } = require( 'cucumber' ),
	{ iClickOnTheCategoryButton,
		iShouldSeeTheCategoriesOverlay, iShouldSeeAListOfCategories
	} = require( './category_steps' ),
	{ iAmInAWikiThatHasCategories,
		iAmOnAPageThatHasTheFollowingEdits,
		iGoToAPageThatHasLanguages } = require( './create_page_api_steps' ),
	{
		pageExists, iAmOnAPageThatDoesNotExist, iShouldSeeAToastNotification,
		iAmUsingTheMobileSite,
		iAmLoggedIntoTheMobileWebsite,
		iAmOnPage, iAmInBetaMode
	} = require( './common_steps' ),
	{
		iShouldSeeAddedContent, iShouldSeeRemovedContent
	} = require( './diff_steps' ),
	{
		iOpenTheLatestDiff,
		iClickTheEditButton, iSeeTheWikitextEditorOverlay, iClearTheEditor,
		iDoNotSeeTheWikitextEditorOverlay,
		iTypeIntoTheEditor, iClickContinue, iClickSubmit, iSayOkayInTheConfirmDialog,
		theTextOfTheFirstHeadingShouldBe, thereShouldBeARedLinkWithText
	} = require( './editor_steps' ),
	{
		iClickOnTheHistoryLinkInTheLastModifiedBar
	} = require( './history_steps' );

defineSupportCode( function ( { Then, When, Given } ) {

	// Editor steps
	Given( /^I click the edit button$/, iClickTheEditButton );
	Then( /^I see the wikitext editor overlay$/, iSeeTheWikitextEditorOverlay );
	When( /^I clear the editor$/, iClearTheEditor );
	When( /^I type "(.+)" into the editor$/, iTypeIntoTheEditor );
	When( /^I click continue$/, iClickContinue );
	When( /^I click submit$/, iClickSubmit );
	When( /^I say OK in the confirm dialog$/, iSayOkayInTheConfirmDialog );
	Then( /^I do not see the wikitext editor overlay$/, iDoNotSeeTheWikitextEditorOverlay );
	Then( /^the text of the first heading should be "(.+)"$/, theTextOfTheFirstHeadingShouldBe );
	Then( /^there should be a red link with text "(.+)"$/, thereShouldBeARedLinkWithText );

	// common steps
	Given( /^I am using the mobile site$/, iAmUsingTheMobileSite );

	Given( /^I am in beta mode$/, iAmInBetaMode );

	Given( /^I am on the "(.+)" page$/, iAmOnPage );

	Given( /^I am logged into the mobile website$/, iAmLoggedIntoTheMobileWebsite );
	Then( /^I should see a toast notification$/, iShouldSeeAToastNotification );

	// Page steps
	Given( /^I am in a wiki that has categories$/, () => {
		iAmInAWikiThatHasCategories( 'Selenium categories test page' );
	} );
	Given( /^I am on a page that has the following edits:$/, iAmOnAPageThatHasTheFollowingEdits );
	Given( /^I am on a page that does not exist$/, iAmOnAPageThatDoesNotExist );
	Given( /^I go to a page that has languages$/, iGoToAPageThatHasLanguages );
	Given( /^the page "(.+)" exists$/, pageExists );

	// history steps
	When( /^I open the latest diff$/, iOpenTheLatestDiff );
	When( /^I click on the history link in the last modified bar$/,
		iClickOnTheHistoryLinkInTheLastModifiedBar );

	// diff steps
	Then( /^I should see "(.*?)" as added content$/, iShouldSeeAddedContent );
	Then( /^I should see "(.*?)" as removed content$/, iShouldSeeRemovedContent );

	// Category steps
	When( /^I click on the category button$/, iClickOnTheCategoryButton );

	Then( /^I should see the categories overlay$/, iShouldSeeTheCategoriesOverlay );

	Then( /^I should see a list of categories$/, iShouldSeeAListOfCategories );
} );
