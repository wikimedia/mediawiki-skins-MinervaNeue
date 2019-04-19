const { iAmViewingAWatchedPage,
		iAmViewingAnUnwatchedPage } = require( '../features/step_definitions/create_page_api_steps' ),
	{
		iShouldSeeAToastNotificationWithMessage,
		iAmLoggedIntoTheMobileWebsite
	} = require( '../features/step_definitions/common_steps' ),
	{
		theWatchstarShouldNotBeSelected, theWatchstarShouldBeSelected,
		iClickTheWatchstar, iClickTheUnwatchStar } = require( '../features/step_definitions/watch_steps' );

// @chrome @smoke @test2.m.wikipedia.org @login @vagrant
describe( 'Manage Watchlist', () => {

	beforeEach( () => {
		iAmLoggedIntoTheMobileWebsite();
	} );

	it( 'Remove an article from the watchlist', () => {
		iAmViewingAWatchedPage();
		iClickTheUnwatchStar();
		iShouldSeeAToastNotificationWithMessage( 'Removed' );
		theWatchstarShouldNotBeSelected();
	} );

	it( 'Add an article to the watchlist', () => {
		iAmViewingAnUnwatchedPage();
		iClickTheWatchstar();
		iShouldSeeAToastNotificationWithMessage( 'Added' );
		theWatchstarShouldBeSelected();
	} );
} );
