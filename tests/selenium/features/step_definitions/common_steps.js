'use strict';

const MWBot = require( 'mwbot' ),
	Api = require( 'wdio-mediawiki/Api' ),
	ArticlePageWithOverlay = require( '../support/pages/article_page_with_overlay' ),
	{ ArticlePage, UserLoginPage } = require( '../support/world.js' );

const createPages = async ( pages ) => {
	const summary = 'edit by selenium test';
	const bot = new MWBot();
	await bot.loginGetEditToken( {
		username: browser.options.username,
		password: browser.options.password,
		apiUrl: `${ browser.options.baseUrl }/api.php`
	} );

	try {
		await bot.batch(
			pages.map( ( page ) => [ 'create' ].concat( page ).concat( [ summary ] ) )
		);
	} catch ( err ) {
		if ( err.code === 'articleexists' ) {
			return;
		}
		throw err;
	}
};

const createPage = async ( title, wikitext ) => {
	const bot = await Api.bot();
	await bot.edit( title, wikitext );
};

const iAmUsingTheMobileSite = async () => {
	await ArticlePage.setMobileMode();
};

const iAmInBetaMode = async () => {
	await ArticlePage.setBetaMode();
};

const iAmOnPage = async ( article ) => {
	await ArticlePage.open( article );
	// Make sure the article opened and JS loaded.
	await ArticlePage.waitUntilResourceLoaderModuleReady( 'skins.minerva.scripts' );
};

const iAmLoggedIn = async () => {
	await UserLoginPage.open();
	await UserLoginPage.loginAdmin();
	await expect( ArticlePage.is_authenticated_element ).toExist();
};

const iAmLoggedIntoTheMobileWebsite = async () => {
	await iAmUsingTheMobileSite();
	await iAmLoggedIn();
};

const pageExists = async ( title ) => {
	await createPage( title, 'Page created by Selenium browser test.' );
};

const pageExistsWithText = async ( title, text ) => {
	await createPage( title, text );
};

const iAmOnAPageThatDoesNotExist = () => iAmOnPage( `NewPage ${ new Date() }` );

const iShouldSeeAToastNotification = async () => {
	await ArticlePage.notification_element.waitForDisplayed();
};

const iShouldSeeAToastNotificationWithMessage = async ( msg ) => {
	await iShouldSeeAToastNotification();
	const notificationBody = await ArticlePage.notification_element.$( '.mw-notification-content' );
	await expect( notificationBody ).toHaveTextContaining( msg );
};

const iClickTheBrowserBackButton = async () => {
	await browser.back();
};

const iClickTheOverlayCloseButton = async () => {
	await ArticlePageWithOverlay.overlay_close_element.waitForDisplayed();
	await ArticlePageWithOverlay.overlay_close_element.click();
};

const iAmUsingMobileScreenResolution = async () => {
	await browser.setWindowSize( 320, 480 );
};

module.exports = {
	iAmUsingMobileScreenResolution,
	iClickTheOverlayCloseButton,
	iClickTheBrowserBackButton,
	createPage, createPages,
	pageExistsWithText,
	pageExists, iAmOnAPageThatDoesNotExist, iShouldSeeAToastNotification,
	iShouldSeeAToastNotificationWithMessage,
	iAmLoggedIntoTheMobileWebsite,
	iAmUsingTheMobileSite,
	iAmLoggedIn, iAmOnPage, iAmInBetaMode
};
