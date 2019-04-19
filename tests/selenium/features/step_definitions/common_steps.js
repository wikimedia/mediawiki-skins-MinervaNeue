const assert = require( 'assert' ),
	Api = require( 'wdio-mediawiki/Api' ),
	{ ArticlePage, UserLoginPage, api } = require( '../support/world.js' );

const login = () => {
	return api.loginGetEditToken( {
		username: browser.options.username,
		password: browser.options.password,
		apiUrl: `${browser.options.baseUrl}/api.php`
	} );
};

const createPages = ( pages ) => {
	const summary = 'edit by selenium test';
	return login().then( () =>
		api.batch(
			pages.map( ( page ) => [ 'create' ].concat( page ).concat( [ summary ] ) )
		)
	);
};

const createPage = ( title, wikitext ) => {
	return login().then( () => Api.edit( title, wikitext ) );
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
	return createPage( title, 'Page created by Selenium browser test.' ).then( () => {
		const d = new Date();
		// wait 2 seconds so the change can propogate.
		browser.waitUntil( () => new Date() - d > 2000 );
	} );
};

const iAmOnAPageThatDoesNotExist = () => {
	return iAmOnPage( `NewPage ${new Date()}` );
};

const iShouldSeeAToastNotification = () => {
	ArticlePage.notification_element.waitForVisible();
};

module.exports = {
	createPage, createPages,
	pageExists, iAmOnAPageThatDoesNotExist, iShouldSeeAToastNotification,
	iAmLoggedIntoTheMobileWebsite,
	iAmUsingTheMobileSite,
	iAmLoggedIn, iAmOnPage, iAmInBetaMode
};
