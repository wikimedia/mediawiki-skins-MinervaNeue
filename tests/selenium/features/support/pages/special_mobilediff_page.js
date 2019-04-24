/**
 * Represents the mobile-first Special:MobileDiff page
 *
 * @extends Page
 * @example
 * https://en.m.wikipedia.org/wiki/Special:MobileDiff/833886807
 */

const { Page } = require( './mw_core_pages' );

class SpecialMobileDiffPage extends Page {

	get user_info_element() { return $( '#mw-mf-userinfo' ); }
}

module.exports = new SpecialMobileDiffPage();
