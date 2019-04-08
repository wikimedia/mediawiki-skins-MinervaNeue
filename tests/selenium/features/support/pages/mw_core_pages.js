/**
 * A list of all MediaWiki core pageObjects.
 * To simplify imports in world.js.
 */
module.exports = {
	// Page is a constructor, all other pageObjects are instances.
	Page: require( '../../../../../../../tests/selenium/pageobjects/page.js' ),
	UserLoginPage: require( '../../../../../../../tests/selenium/pageobjects/userlogin.page.js' )
};
