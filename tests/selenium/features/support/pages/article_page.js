/**
 * Represents a generic article page
 *
 * @extends MinervaPage
 * @example
 * https://en.m.wikipedia.org/wiki/Barack_Obama
 */

const MinervaPage = require( './minerva_page' );

class ArticlePage extends MinervaPage {

	get category_element() { return $( '.category-button' ); }
	get overlay_heading_element() { return $( '.overlay-title h2' ); }
	get overlay_category_topic_item_element() { return $( '.topic-title-list li' ); }
	get is_authenticated_element() { return $( 'body.is-authenticated' ); }
	get last_modified_bar_history_link_element() { return $( '.last-modifier-tagline a[href*=\'Special:History\']' ); }

}

module.exports = new ArticlePage();
