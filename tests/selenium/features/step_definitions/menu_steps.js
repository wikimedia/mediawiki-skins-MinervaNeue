const assert = require( 'assert' );
const { ArticlePage } = require( '../support/world.js' );

const iSeeALinkToAboutPage = () => {
	assert.strictEqual( ArticlePage.menu_element.element( '*=About' ).isVisible(), true );
};

const iClickOnTheMainNavigationButton = () => {
	ArticlePage.menu_button_element.click();
};

const iShouldSeeAUserPageLinkInMenu = () => {
	ArticlePage.menu_element.element( '.mw-ui-icon-minerva-profile' );
};

const iShouldSeeALinkInMenu = ( text ) => {
	assert.strictEqual( ArticlePage.menu_element.element( `=${text}` ).isVisible(),
		true, `Link to ${text} is visible.` );
};

const iShouldSeeALinkToDisclaimer = () => {
	assert.strictEqual( ArticlePage.menu_element.element( '=Disclaimers' ).isVisible(), true );
};

module.exports = {
	iClickOnTheMainNavigationButton,
	iSeeALinkToAboutPage, iShouldSeeAUserPageLinkInMenu,
	iShouldSeeALinkInMenu, iShouldSeeALinkToDisclaimer
};
