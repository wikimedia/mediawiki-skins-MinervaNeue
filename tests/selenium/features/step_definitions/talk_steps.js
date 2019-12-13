const assert = require( 'assert' );
const { iSeeAnOverlay, waitForPropagation } = require( './common_steps' );
const ArticlePageWithEditorOverlay = require( '../support/pages/article_page_with_editor_overlay' );
const { ArticlePage } = require( '../support/world.js' );

const iClickTheAddTalkButton = () => {
	ArticlePage.waitUntilResourceLoaderModuleReady( 'skins.minerva.scripts' );
	ArticlePage.talk_add_element.waitForVisible();
	ArticlePage.talk_add_element.click();
};

const iAddATopic = ( subject ) => {
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
	assert.strictEqual( ArticlePage.first_section_element.isExisting(), false );
};

const thereShouldBeAnAddDiscussionButton = () => {
	assert.strictEqual( ArticlePage.talk_add_element.isVisible(), true );
};

const thereShouldBeATalkButton = () => {
	assert.strictEqual( ArticlePage.talk_element.isVisible(), true );
};

const thereShouldBeNoTalkButton = () => {
	assert.strictEqual( ArticlePage.talk_element.isVisible(), false );
};

const iShouldSeeTheTopicInTheListOfTopics = ( subject ) => {
	assert.strictEqual(
		ArticlePage.first_section_element.getText().indexOf( subject ) > -1,
		true
	);
};

const thereShouldBeATalkTab = () => {
	assert.strictEqual( ArticlePage.talk_tab_element.isVisible(), true );
};

module.exports = {
	iAddATopic,
	iSeeTheTalkOverlay,
	thereShouldBeASaveDiscussionButton,
	noTopicIsPresent,
	thereShouldBeAnAddDiscussionButton,
	thereShouldBeATalkTab,
	thereShouldBeATalkButton,
	thereShouldBeNoTalkButton,
	iShouldSeeTheTopicInTheListOfTopics,
	iClickTheAddTalkButton
};
