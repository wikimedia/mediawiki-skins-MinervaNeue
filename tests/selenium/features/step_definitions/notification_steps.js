'use strict';

const ArticlePage = require( '../support/pages/article_page' );
const { iClickTheOverlayCloseButton } = require( './common_steps' );

const iHaveNoNotifications = () => {
	ArticlePage.notifications_button_element.waitForDisplayed();
	// This is somewhat hacky, but we don't want this test making use of
	// Echo's APIs which may change
	browser.execute( '$( () => { $( ".notification-count span" ).hide(); } );' );
};

const iClickOnTheNotificationIcon = () => {
	ArticlePage.waitUntilResourceLoaderModuleReady( 'skins.minerva.scripts' );
	ArticlePage.notifications_button_element.waitForDisplayed();
	ArticlePage.notifications_button_element.click();
};

const iClickTheNotificationsOverlayCloseButton = () => {
	iClickTheOverlayCloseButton();
};

module.exports = {
	iHaveNoNotifications, iClickOnTheNotificationIcon,
	iClickTheNotificationsOverlayCloseButton
};
