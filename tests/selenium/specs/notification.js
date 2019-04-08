const
	{
		iClickTheBrowserBackButton,
		iAmLoggedIntoTheMobileWebsite
	} = require( './../features/step_definitions/common_steps' ),
	{ iHaveNoNotifications, iClickOnTheNotificationIcon,
		iShouldSeeTheNotificationsOverlay, iClickTheNotificationsOverlayCloseButton,
		iShouldNotSeeTheNotificationsOverlay
	} = require( './../features/step_definitions/notification_steps' );

// @chrome @en.m.wikipedia.beta.wmflabs.org @extension-echo
// @firefox @vagrant @login
describe( 'Feature: Notification', () => {

	beforeEach( () => {
		iAmLoggedIntoTheMobileWebsite();
		iHaveNoNotifications();
		iClickOnTheNotificationIcon();
		iShouldSeeTheNotificationsOverlay();
	} );

	it( 'Closing notifications (overlay button)', () => {
		iClickTheNotificationsOverlayCloseButton();
		iShouldNotSeeTheNotificationsOverlay();
	} );

	it( 'Closing notifications (browser button)', () => {
		iClickTheBrowserBackButton();
		iShouldNotSeeTheNotificationsOverlay();
	} );
} );
