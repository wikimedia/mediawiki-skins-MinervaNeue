const assert = require( 'assert' ),
	{ ArticlePage, UserLoginPage } = require( '../support/world' );

const iAmUsingTheMobileSite = () => {
	ArticlePage.setMobileMode();
};

const iAmInBetaMode = () => {
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
