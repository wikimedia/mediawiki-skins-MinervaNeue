const assert = require( 'assert' ),
	MWBot = require( 'mwbot' ),
	Api = require( 'wdio-mediawiki/Api' ),
	ArticlePageWithOverlay = require( '../support/pages/article_page_with_overlay' ),
	{ ArticlePage, UserLoginPage } = require( '../support/world.js' );

const waitForPropagation = ( timeMs ) => {
	// wait 2 seconds so the change can propogate.
	const d = new Date();
	browser.waitUntil( () => new Date() - d > timeMs );
};

const createPages = ( pages ) => {
	const summary = 'edit by selenium test';
	browser.call( () => {
		const bot = new MWBot();
		return bot.loginGetEditToken( {
			username: browser.options.username,
			password: browser.options.password,
			apiUrl: `${browser.options.baseUrl}/api.php`
		} )
			.then( () => {
				return bot.batch(
					pages.map( ( page ) => [ 'create' ].concat( page ).concat( [ summary ] ) )
				).catch( ( err ) => {
					if ( err.code === 'articleexists' ) {
						return;
					}
					throw err;
				} );
			} )
			.catch( ( err ) => { throw err; } );
	} );
};

const createPage = ( title, wikitext ) => {
	browser.call( () => Api.edit( title, wikitext ) );
};

const iAmUsingTheMobileSite = () => {
	ArticlePage.setMobileMode();
};

const iAmInBetaMode = () => {
	ArticlePage.setBetaMode();
};

const iAmOnPage = ( article ) => {
	ArticlePage.open( article );
	// Make sure the article opened and JS loaded.
	ArticlePage.waitUntilResourceLoaderModuleReady( 'skins.minerva.scripts' );
};

const iAmLoggedIn = () => {
	UserLoginPage.open();
	UserLoginPage.loginAdmin();
	assert.strictEqual( ArticlePage.is_authenticated_element.isExisting(), true );
};

const iAmLoggedIntoTheMobileWebsite = () => {
	iAmUsingTheMobileSite();
	iAmLoggedIn();
};

const pageExists = ( title ) => {
	browser.call( () =>
		createPage( title, 'Page created by Selenium browser test.' )
	);
	// wait 2 seconds so the change can propogate.
	waitForPropagation( 2000 );
};

const iAmOnAPageThatDoesNotExist = () => {
	return iAmOnPage( `NewPage ${new Date()}` );
};

const iShouldSeeAToastNotification = () => {
	ArticlePage.notification_element.waitForVisible();
};

const iShouldSeeAToastNotificationWithMessage = ( msg ) => {
	iShouldSeeAToastNotification();
	const notificationBody = ArticlePage.notification_element.element( '.mw-notification-content' );
	assert.strictEqual( notificationBody.getText().indexOf( msg ) > -1, true );
};

const iClickTheBrowserBackButton = () => {
	browser.back();
};

const iClickTheOverlayCloseButton = () => {
	waitForPropagation( 2000 );
	ArticlePageWithOverlay.overlay_close_element.click();
};

const iSeeAnOverlay = () => {
	ArticlePageWithOverlay.overlay_element.waitForVisible();
	assert.strictEqual( ArticlePageWithOverlay.overlay_element.isVisible(), true );
};

const iDoNotSeeAnOverlay = () => {
	waitForPropagation( 5000 );
	browser.waitUntil( () => !ArticlePageWithOverlay.overlay_element.isVisible() );
	assert.strictEqual( ArticlePageWithOverlay.overlay_element.isVisible(), false );
};

const iAmUsingMobileScreenResolution = () => {
	browser.setViewportSize( { width: 320, height: 480 }, true );
};

module.exports = {
	waitForPropagation,
	iAmUsingMobileScreenResolution,
	iSeeAnOverlay, iDoNotSeeAnOverlay,
	iClickTheOverlayCloseButton,
	iClickTheBrowserBackButton,
	createPage, createPages,
	pageExists, iAmOnAPageThatDoesNotExist, iShouldSeeAToastNotification,
	iShouldSeeAToastNotificationWithMessage,
	iAmLoggedIntoTheMobileWebsite,
	iAmUsingTheMobileSite,
	iAmLoggedIn, iAmOnPage, iAmInBetaMode
};
