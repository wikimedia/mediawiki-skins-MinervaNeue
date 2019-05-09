const {
		pageExists, iShouldSeeAToastNotification,
		iAmUsingMobileScreenResolution,
		iAmUsingTheMobileSite,
		iAmLoggedIntoTheMobileWebsite,
		iAmOnPage
	} = require( '../features/step_definitions/common_steps' ),
	{
		iClickTheSearchIcon,
		iTypeIntoTheSearchBox,
		iClickASearchWatchstar,
		iSeeTheSearchOverlay
	} = require( '../features/step_definitions/search_steps' );

// @test2.m.wikipedia.org @vagrant @login
describe.skip( 'Search', () => {

	it( 'Clicking on a watchstar toggles the watchstar', () => {
		iAmUsingTheMobileSite();
		pageExists( 'Selenium search test' );
		iAmLoggedIntoTheMobileWebsite();
		iAmOnPage( 'Main Page' );
		iAmUsingMobileScreenResolution();
		iClickTheSearchIcon();
		iSeeTheSearchOverlay();
		iTypeIntoTheSearchBox( 'Selenium search tes' );
		iClickASearchWatchstar();
		iShouldSeeAToastNotification();
	} );
} );
