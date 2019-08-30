const assert = require( 'assert' );
const { ArticlePage } = require( '../support/world.js' );

const iSeeALinkToAboutPage = () => {
	assert.strictEqual( ArticlePage.menu_element.element( '*=About' ).isVisible(), true );
};

const iClickOnTheMainNavigationButton = () => {
	ArticlePage.menu_button_element.click();
};

const iShouldSeeAUserPageLinkInMenu = () => {
	ArticlePage.menu_element.element( '.primary-action' );
};

const iShouldSeeLogoutLinkInMenu = () => {
	ArticlePage.menu_element.element( '.secondary-action' );
};

const iShouldSeeALinkInMenu = ( text ) => {
	assert.strictEqual( ArticlePage.menu_element.element( `span=${text}` ).isVisible(),
		true, `Link to ${text} is visible.` );
};

const iShouldSeeALinkToDisclaimer = () => {
	ArticlePage.menu_element.element( '=Disclaimers' ).waitForVisible();
	assert.strictEqual( ArticlePage.menu_element.element( '=Disclaimers' ).isVisible(), true );
};

module.exports = {
	iClickOnTheMainNavigationButton,
	iSeeALinkToAboutPage, iShouldSeeAUserPageLinkInMenu,
	iShouldSeeLogoutLinkInMenu,
	iShouldSeeALinkInMenu, iShouldSeeALinkToDisclaimer
};
