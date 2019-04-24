/**
 * Represents a page showing the difference between two revisions
 *
 * @extends Page
 * @example
 * https://en.m.wikipedia.org/w/index.php?title=Barack_Obama&type=revision&diff=833886807&oldid=833885770&diffmode=source
 */

const { Page } = require( './mw_core_pages' );

class DiffPage extends Page {

	get inserted_content_element() { return $( 'ins' ); }
	get deleted_content_element() { return $( 'del' ); }
}

module.exports = new DiffPage();
