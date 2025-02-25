'use strict';

const { ArticlePageWithEditorOverlay, ArticlePage } = require( '../support/world.js' );

const iClickTheEditButton = async () => {
	await ArticlePage.edit_link_element.waitForDisplayed();
	await ArticlePage.edit_link_element.click();
};
const iSeeTheWikitextEditorOverlay = async () => {
	await ArticlePageWithEditorOverlay.editor_overlay_element.waitForDisplayed();
	await ArticlePageWithEditorOverlay.editor_textarea_element.waitForExist();
};
const iClearTheEditor = () => {
	ArticlePageWithEditorOverlay.editor_textarea_element.setValue( '' );
};
const iDoNotSeeTheWikitextEditorOverlay = () => {
	browser.waitUntil(
		() => ArticlePageWithEditorOverlay.editor_overlay_element.isDisplayed() === false, 10000
	);
};
const iTypeIntoTheEditor = ( text ) => {
	ArticlePageWithEditorOverlay.editor_overlay_element.waitForExist();
	ArticlePageWithEditorOverlay.editor_textarea_element.waitForExist();
	ArticlePageWithEditorOverlay.editor_textarea_element.waitForDisplayed();
	// Make sure the slow connection load basic button is gone (T348539)
	browser.waitUntil(
		() => ArticlePageWithEditorOverlay.editor_load_basic_element.isDisplayed() === false
	);
	ArticlePageWithEditorOverlay.editor_textarea_element.addValue( text );
	browser.waitUntil( () => !ArticlePageWithEditorOverlay
		.continue_element.getAttribute( 'disabled' ) );
};
const iClickContinue = () => {
	ArticlePageWithEditorOverlay.continue_element.waitForExist();
	ArticlePageWithEditorOverlay.continue_element.click();
};
const iClickSubmit = () => {
	ArticlePageWithEditorOverlay.submit_element.waitForExist();
	ArticlePageWithEditorOverlay.submit_element.click();
};
const iSayOkayInTheConfirmDialog = () => {
	browser.waitUntil( () => {
		try {
			const text = browser.getAlertText();
			return text && true;
		} catch ( e ) {
			return false;
		}
	}, 2000 );
	browser.acceptAlert();
};

module.exports = {
	iClickTheEditButton, iSeeTheWikitextEditorOverlay, iClearTheEditor,
	iDoNotSeeTheWikitextEditorOverlay,
	iTypeIntoTheEditor, iClickContinue, iClickSubmit, iSayOkayInTheConfirmDialog
};
