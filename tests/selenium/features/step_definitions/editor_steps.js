const assert = require( 'assert' );
const { ArticlePageWithEditorOverlay, ArticlePage } = require( '../support/world.js' );

const iClickTheEditButton = () => {
	ArticlePage.edit_link_element.waitForVisible();
	ArticlePage.edit_link_element.click();
};
const iSeeTheWikitextEditorOverlay = () => {
	ArticlePageWithEditorOverlay.editor_overlay_element.waitForVisible();
	ArticlePageWithEditorOverlay.editor_textarea_element.waitForExist();
};
const iClearTheEditor = () => {
	ArticlePageWithEditorOverlay.editor_textarea_element.setValue( '' );
};
const iDoNotSeeTheWikitextEditorOverlay = () => {
	browser.waitUntil( () => {
		return ArticlePageWithEditorOverlay.editor_overlay_element.isVisible() === false;
	}, 10000 );
};
const iTypeIntoTheEditor = ( text ) => {
	ArticlePageWithEditorOverlay.editor_overlay_element.waitForExist();
	ArticlePageWithEditorOverlay.editor_textarea_element.waitForExist();
	ArticlePageWithEditorOverlay.editor_textarea_element.waitForVisible();
	ArticlePageWithEditorOverlay.editor_textarea_element.addValue( text );
	browser.waitUntil( () => {
		return !ArticlePageWithEditorOverlay
			.continue_element.getAttribute( 'disabled' );
	} );
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
			const text = browser.alertText;
			return text && true;
		} catch ( e ) {
			return false;
		}
	}, 2000 );
	browser.alertAccept();
};
const theTextOfTheFirstHeadingShouldBe = ( title ) => {
	ArticlePage.first_heading_element.waitForVisible();
	assert.strictEqual(
		ArticlePage.first_heading_element.getText(),
		title
	);
};
const thereShouldBeARedLinkWithText = ( text ) => {
	ArticlePage.red_link_element.waitForExist();
	assert.strictEqual(
		ArticlePage.red_link_element.getText(),
		text
	);
};

module.exports = {
	iClickTheEditButton, iSeeTheWikitextEditorOverlay, iClearTheEditor,
	thereShouldBeARedLinkWithText,
	iDoNotSeeTheWikitextEditorOverlay,
	iTypeIntoTheEditor, iClickContinue, iClickSubmit, iSayOkayInTheConfirmDialog,
	theTextOfTheFirstHeadingShouldBe
};
