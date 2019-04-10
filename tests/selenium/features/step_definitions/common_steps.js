const assert = require( 'assert' ),
	{ ArticlePage, UserLoginPage } = require( '../support/world' );

const iAmUsingTheMobileSite = () => {
	ArticlePage.setMobileMode();
};

const iAmInBetaMode = () => {
	// running beta mode requires being on the mobile domain
	iAmUsingTheMobileSite();
	// and making sure the browser URL is set to the mobile domain by triggering a page load
	ArticlePage.open( 'Page on the mobile domain' );
	// Cookie will now set on mobile domain
	ArticlePage.setBetaMode();
};

const iAmOnPage = ( article ) => {
	ArticlePage.open( article );
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

module.exports = {
	iAmLoggedIntoTheMobileWebsite,
	iAmUsingTheMobileSite,
	iAmLoggedIn, iAmOnPage, iAmInBetaMode
};
