'use strict';

const MWBot = require( 'mwbot' ),
	Api = require( 'wdio-mediawiki/Api' ),
	ArticlePageWithOverlay = require( '../support/pages/article_page_with_overlay' ),
	{ ArticlePage, UserLoginPage } = require( '../support/world.js' );

const waitForPropagation = async ( timeMs ) => {
	// wait 2 seconds so the change can propogate.
	// Replace this with a more dynamic statement.
	// eslint-disable-next-line wdio/no-pause
	await browser.pause( timeMs );
};

const createPages = ( pages ) => {
	const summary = 'edit by selenium test';
	browser.call( () => {
		const bot = new MWBot();
		return bot.loginGetEditToken( {
			username: browser.options.username,
			password: browser.options.password,
			apiUrl: `${ browser.options.baseUrl }/api.php`
		} )
			.then( () => bot.batch(
				pages.map( ( page ) => [ 'create' ].concat( page ).concat( [ summary ] ) )
			).catch( ( err ) => {
				if ( err.code === 'articleexists' ) {
					return;
				}
				throw err;
			} ) )
			.catch( ( err ) => {
				throw err;
			} );
	} );
};

const createPage = ( title, wikitext ) => {
	browser.call( async () => {
		const bot = await Api.bot();
		await bot.edit( title, wikitext );
	} );
};

const iAmUsingTheMobileSite = async () => {
	await ArticlePage.setMobileMode();
};

const iAmInBetaMode = () => {
	ArticlePage.setBetaMode();
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
	await browser.call( async () => await createPage( title, 'Page created by Selenium browser test.' )
	);
	// wait 2 seconds so the change can propogate.
	await waitForPropagation( 2000 );
};

const pageExistsWithText = ( title, text ) => {
	browser.call( () => createPage( title, text )
	);
	// wait 2 seconds so the change can propogate.
	waitForPropagation( 2000 );
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

const iClickTheOverlayCloseButton = () => {
	waitForPropagation( 2000 );
	ArticlePageWithOverlay.overlay_close_element.waitForDisplayed();
	ArticlePageWithOverlay.overlay_close_element.click();
};

const iAmUsingMobileScreenResolution = async () => {
	await browser.setWindowSize( 320, 480 );
};

module.exports = {
	waitForPropagation,
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
