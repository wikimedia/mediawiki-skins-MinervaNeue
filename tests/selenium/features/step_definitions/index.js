const { defineSupportCode } = require( '@cucumber/cucumber' ),
	{
		pageExists,
		iAmOnAPageThatDoesNotExist,
		iShouldSeeAToastNotification,
		iAmUsingMobileScreenResolution,
		iAmUsingTheMobileSite,
		iClickTheBrowserBackButton,
		iClickTheOverlayCloseButton,
		iAmLoggedIntoTheMobileWebsite,
		iAmOnPage
	} = require( './common_steps' ),
	{
		iClickTheEditButton, iSeeTheWikitextEditorOverlay, iClearTheEditor,
		iDoNotSeeTheWikitextEditorOverlay,
		iTypeIntoTheEditor, iClickContinue, iClickSubmit, iSayOkayInTheConfirmDialog
	} = require( './editor_steps' ),
	{ iVisitMyUserPage, iShouldBeOnMyUserPage
	} = require( './user_page_steps' ),
	{
		iClickTheSearchIcon,
		iTypeIntoTheSearchBox,
		iSeeTheSearchOverlay
	} = require( './search_steps' ),
	{ iSeeALinkToAboutPage, iShouldSeeAUserPageLinkInMenu,
		iClickOnTheMainNavigationButton,
		iShouldSeeALinkInMenu, iShouldSeeALinkToDisclaimer
	} = require( './menu_steps' );

defineSupportCode( ( { Then, When, Given } ) => {

	// Editor steps
	Given( /^I click the edit button$/, iClickTheEditButton );
	Then( /^I see the wikitext editor overlay$/, iSeeTheWikitextEditorOverlay );
	When( /^I clear the editor$/, iClearTheEditor );
	When( /^I type "(.+)" into the editor$/, iTypeIntoTheEditor );
	When( /^I click continue$/, iClickContinue );
	When( /^I click submit$/, iClickSubmit );
	When( /^I say OK in the confirm dialog$/, iSayOkayInTheConfirmDialog );
	When( /^I click the wikitext editor overlay close button$/, iClickTheOverlayCloseButton );
	Then( /^I do not see the wikitext editor overlay$/, iDoNotSeeTheWikitextEditorOverlay );

	// common steps
	Given( /^I am using the mobile site$/, iAmUsingTheMobileSite );
	When( /^I am viewing the site in mobile mode$/, iAmUsingMobileScreenResolution );

	Given( /^I am on the "(.+)" page$/, iAmOnPage );

	Given( /^I am logged into the mobile website$/, iAmLoggedIntoTheMobileWebsite );
	Then( /^I should see a toast notification$/, iShouldSeeAToastNotification );
	When( /I click the browser back button/, iClickTheBrowserBackButton );

	// Page steps
	Given( /^I am on a page that does not exist$/, iAmOnAPageThatDoesNotExist );
	Given( /^the page "(.+)" exists$/, pageExists );

	// user page
	Given( /^I visit my user page$/, iVisitMyUserPage );
	When( /^I should be on my user page$/, iShouldBeOnMyUserPage );

	// search
	When( /^I click the search icon$/, iClickTheSearchIcon );
	When( /^I type into search box "(.+)"$/, iTypeIntoTheSearchBox );
	Then( /^I see the search overlay$/, iSeeTheSearchOverlay );

	// main menu
	When( /^I click on the main navigation button$/, iClickOnTheMainNavigationButton );
	When( /^I should see a link to the about page$/, iSeeALinkToAboutPage );
	Then( /^I should see a link to my user page in the main navigation menu$/, iShouldSeeAUserPageLinkInMenu );
	Then( /^I should see a link to "(.+)" in the main navigation menu$/, iShouldSeeALinkInMenu );
	Then( /^I should see a link to the disclaimer$/, iShouldSeeALinkToDisclaimer );
} );
