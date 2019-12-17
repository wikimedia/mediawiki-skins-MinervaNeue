const { ArticlePage } = require( './../support/world' );

const iClickOnTheMask = () => {
	ArticlePage.mask_element.waitForVisible();
	ArticlePage.mask_element.click();
};

const iShouldSeeNotTheReferenceDrawer = () => {
	browser.waitUntil( () => !ArticlePage.drawer_element.isVisible() );
};

const iClickOnAReference = () => {
	ArticlePage.reference_element.click();
};

const iClickOnANestedReference = () => {
	ArticlePage.drawer_reference_element.waitForVisible();
	ArticlePage.drawer_reference_element.click();
};

const iShouldSeeDrawerWithText = ( text ) => {
	ArticlePage.drawer_element.waitForVisible();
	browser.waitUntil( () => ArticlePage.drawer_element.getText().indexOf( text ) > -1 );
};

module.exports = {
	iClickOnAReference,
	iClickOnTheMask,
	iShouldSeeNotTheReferenceDrawer,
	iClickOnANestedReference,
	iShouldSeeDrawerWithText
};
