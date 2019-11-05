const assert = require( 'assert' );
const { iSeeAnOverlay, waitForPropagation } = require( './common_steps' );
const ArticlePageWithEditorOverlay = require( '../support/pages/article_page_with_editor_overlay' );
const { ArticlePage } = require( '../support/world.js' );

const iClickTheTalkButton = () => {
	ArticlePage.waitUntilResourceLoaderModuleReady( 'skins.minerva.scripts' );
	ArticlePage.talk_element.waitForVisible();
	ArticlePage.talk_element.click();
};

const iAddATopic = ( subject ) => {
	ArticlePageWithEditorOverlay.continue_element.waitForVisible();
	ArticlePageWithEditorOverlay.continue_element.click();
	ArticlePageWithEditorOverlay.editor_overlay_element.waitForExist();
	const overlay = ArticlePageWithEditorOverlay.editor_overlay_element;
	overlay.element( '.overlay input' ).waitForExist();
	overlay.element( '.overlay input' ).setValue( subject );
	overlay.element( '.overlay textarea' ).setValue( 'Topic body is a really long text.' );
	browser.waitUntil( () =>
		!ArticlePageWithEditorOverlay.submit_element.getAttribute( 'disabled' )
	);
	ArticlePageWithEditorOverlay.submit_element.click();
	waitForPropagation( 5000 );
};

const iSeeTheTalkOverlay = () => {
	iSeeAnOverlay();
};

const thereShouldBeASaveDiscussionButton = () => {
	const submit = ArticlePageWithEditorOverlay.submit_element;
	submit.waitForExist();
	assert.strictEqual( submit.isVisible(), true );
};

const noTopicIsPresent = () => {
	ArticlePageWithEditorOverlay.editor_overlay_element.waitForExist();
	const overlay = ArticlePageWithEditorOverlay.editor_overlay_element;
	overlay.element( '.content-header' ).waitForExist();
	assert.strictEqual(
		overlay.element( '.content-header' ).getText(),
		'There are no conversations about this page.'
	);
};

const thereShouldBeAnAddDiscussionButton = () => {
	ArticlePageWithEditorOverlay.continue_element.waitForVisible();
};

const thereShouldBeNoTalkButton = () => {
	assert.strictEqual( ArticlePage.talk_element.isVisible(), false );
};

const iShouldSeeTheTopicInTheListOfTopics = ( subject ) => {
	ArticlePageWithEditorOverlay.editor_overlay_element.waitForExist();
	ArticlePageWithEditorOverlay.editor_overlay_element.element( '.topic-title-list li' ).waitForExist();
	const firstItem = ArticlePageWithEditorOverlay.editor_overlay_element.element( '.topic-title-list li' );
	assert.strictEqual(
		firstItem.getText().indexOf( subject ) > -1,
		true
	);
};

module.exports = {
	iAddATopic,
	iSeeTheTalkOverlay,
	thereShouldBeASaveDiscussionButton,
	noTopicIsPresent,
	thereShouldBeAnAddDiscussionButton,
	thereShouldBeNoTalkButton,
	iShouldSeeTheTopicInTheListOfTopics,
	iClickTheTalkButton
};
