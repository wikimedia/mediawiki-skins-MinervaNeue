import { ArticlePage } from './../support/world.js';

const iClickOnTheMask = async () => {
	await ArticlePage.drawer_mask_element.waitForDisplayed();
	await ArticlePage.drawer_mask_element.click();
};

const iShouldSeeNotTheReferenceDrawer = async () => {
	await browser.waitUntil( async () => !( await ArticlePage.drawer_element.isDisplayed() ) );
};

const iClickOnAReference = async () => {
	await ArticlePage.reference_element.waitForDisplayed();
	await ArticlePage.reference_element.click();
};

const iShouldSeeDrawerWithText = async ( text ) => {
	await ArticlePage.drawer_element.waitForDisplayed();
	await browser.waitUntil( async () => ( await ArticlePage.drawer_element.getText() ).includes( text ) );
};

export {
	iClickOnAReference,
	iClickOnTheMask,
	iShouldSeeNotTheReferenceDrawer,
	iShouldSeeDrawerWithText
};
