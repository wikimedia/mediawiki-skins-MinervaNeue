const { defineSupportCode } = require( '@cucumber/cucumber' ),
	{ iAmInAWikiThatHasCategories,
		iAmOnAPageThatHasTheFollowingEdits,
		iAmOnATalkPageWithNoTalkTopics,
		iAmViewingAWatchedPage, iAmViewingAnUnwatchedPage,
		iGoToAPageThatHasLanguages } = require( './create_page_api_steps' ),
	{
		pageExists, iAmOnAPageThatDoesNotExist, iShouldSeeAToastNotification,
		iShouldSeeAToastNotificationWithMessage, iAmUsingMobileScreenResolution,
		iAmUsingTheMobileSite, iClickTheBrowserBackButton,
		iClickTheOverlayCloseButton,
		iAmLoggedIntoTheMobileWebsite,
		iAmOnPage, iAmInBetaMode
	} = require( './common_steps' ),
	{
		iClickTheEditButton, iSeeTheWikitextEditorOverlay, iClearTheEditor,
		iDoNotSeeTheWikitextEditorOverlay,
		iTypeIntoTheEditor, iClickContinue, iClickSubmit, iSayOkayInTheConfirmDialog
	} = require( './editor_steps' ),
	{
		theWatchstarShouldBeSelected,
		iClickTheWatchstar, iClickTheUnwatchStar } = require( './watch_steps' ),
	{ iVisitMyUserPage, iShouldBeOnMyUserPage
	} = require( './user_page_steps' ),
	{
		iClickTheSearchIcon,
		iTypeIntoTheSearchBox,
		iClickASearchWatchstar,
		iSeeTheSearchOverlay
	} = require( './search_steps' ),
	{ iSeeALinkToAboutPage, iShouldSeeAUserPageLinkInMenu,
		iClickOnTheMainNavigationButton,
		iShouldSeeALinkInMenu, iShouldSeeALinkToDisclaimer
	} = require( './menu_steps' ),
	{ iHaveNoNotifications, iClickOnTheNotificationIcon,
		iShouldSeeTheNotificationsOverlay, iClickTheNotificationsOverlayCloseButton,
		iShouldNotSeeTheNotificationsOverlay
	} = require( './notification_steps' );

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

	Given( /^I am in beta mode$/, iAmInBetaMode );

	Given( /^I am on the "(.+)" page$/, iAmOnPage );

	Given( /^I am logged into the mobile website$/, iAmLoggedIntoTheMobileWebsite );
	Then( /^I should see a toast notification$/, iShouldSeeAToastNotification );
	Then( /^I should see a toast with message "(.+)"$/, iShouldSeeAToastNotificationWithMessage );
	When( /I click the browser back button/, iClickTheBrowserBackButton );

	// Page steps
	Given( /^I am on a talk page with no talk topics$/, iAmOnATalkPageWithNoTalkTopics );
	Given( /^I am in a wiki that has categories$/, () => {
		iAmInAWikiThatHasCategories( 'Selenium categories test page' );
	} );
	Given( /^I am on a page that has the following edits:$/, iAmOnAPageThatHasTheFollowingEdits );
	Given( /^I am on a page that does not exist$/, iAmOnAPageThatDoesNotExist );
	Given( /^I go to a page that has languages$/, iGoToAPageThatHasLanguages );
	Given( /^the page "(.+)" exists$/, pageExists );
	Given( /^I am viewing a watched page$/, iAmViewingAWatchedPage );
	Given( /^I am viewing an unwatched page$/, iAmViewingAnUnwatchedPage );

	// notifications
	Then( /I have no notifications/, iHaveNoNotifications );
	When( /I click on the notification icon/, iClickOnTheNotificationIcon );
	When( /I click the notifications overlay close button/, iClickTheNotificationsOverlayCloseButton );
	Then( /after 1 seconds I should not see the notifications overlay/, iShouldNotSeeTheNotificationsOverlay );
	Then( /I should see the notifications overlay/, iShouldSeeTheNotificationsOverlay );

	// user page
	Given( /^I visit my user page$/, iVisitMyUserPage );
	When( /^I should be on my user page$/, iShouldBeOnMyUserPage );

	// search
	When( /^I click the search icon$/, iClickTheSearchIcon );
	When( /^I type into search box "(.+)"$/, iTypeIntoTheSearchBox );
	When( /^I click a search watch star$/, iClickASearchWatchstar );
	Then( /^I see the search overlay$/, iSeeTheSearchOverlay );

	// main menu
	When( /^I click on the main navigation button$/, iClickOnTheMainNavigationButton );
	When( /^I should see a link to the about page$/, iSeeALinkToAboutPage );
	Then( /^I should see a link to my user page in the main navigation menu$/, iShouldSeeAUserPageLinkInMenu );
	Then( /^I should see a link to "(.+)" in the main navigation menu$/, iShouldSeeALinkInMenu );
	Then( /^I should see a link to the disclaimer$/, iShouldSeeALinkToDisclaimer );

	// watchstar
	When( /^I click the watch star$/, iClickTheWatchstar );
	When( /^I click the unwatch star$/, iClickTheUnwatchStar );
	Then( /^the watch star should be selected$/, theWatchstarShouldBeSelected );

} );
