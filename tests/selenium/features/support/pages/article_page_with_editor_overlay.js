/**
 * Represents a generic article page with the editor overlay open
 *
 * @class ArticlePageWithEditorOverlay
 * @extends MinervaPage
 * @example
 * https://en.m.wikipedia.org/wiki/Barack_Obama#/editor/0
 */

const MinervaPage = require( './minerva_page' );

class ArticlePageWithEditorOverlay extends MinervaPage {
	get editor_overlay_element() { return $( '.editor-overlay' ); }

	// overlay components
	get editor_textarea_element() { return $( '.editor-overlay .wikitext-editor' ); }
	get continue_element() { return $( '.editor-overlay .continue' ); }
	get submit_element() { return $( '.editor-overlay .submit' ); }
}

module.exports = new ArticlePageWithEditorOverlay();
