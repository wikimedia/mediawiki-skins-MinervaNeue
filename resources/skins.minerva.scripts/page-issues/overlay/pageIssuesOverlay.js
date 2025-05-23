const Overlay = require( 'mobile.startup' ).Overlay;
const issueList = require( './IssueList.js' );
const KEYWORD_ALL_SECTIONS = 'all';
const namespaceIds = mw.config.get( 'wgNamespaceIds' );
const NS_MAIN = namespaceIds[ '' ];
const NS_CATEGORY = namespaceIds.category;

/**
 * Overlay for displaying page issues
 *
 * @ignore
 * @param {IssueSummary[]} issues List of page issue
 *  summaries for display.
 * @param {string} section
 * @param {number} namespaceID
 * @return {Overlay}
 */
function pageIssuesOverlay( issues, section, namespaceID ) {
	// Note only the main namespace is expected to make use of section issues, so the
	// heading will always be minerva-meta-data-issues-section-header regardless of
	// namespace.
	const headingText = section === '0' || section === KEYWORD_ALL_SECTIONS ?
		getNamespaceHeadingText( namespaceID ) :
		mw.msg( 'minerva-meta-data-issues-section-header' );

	return Overlay.make( {
		className: 'overlay overlay-issues',
		heading: `<strong>${ headingText }</strong>`
	}, issueList( issues ) );
}

/**
 * Obtain a suitable heading for the issues overlay based on the namespace
 *
 * @private
 * @param {number} namespaceID is the namespace to generate heading for
 * @return {string} heading for overlay
 */
function getNamespaceHeadingText( namespaceID ) {
	switch ( namespaceID ) {
		case NS_CATEGORY:
			return mw.msg( 'mobile-frontend-meta-data-issues-categories' );
		case NS_MAIN:
			return mw.msg( 'mobile-frontend-meta-data-issues' );
		default:
			return '';
	}
}

module.exports = pageIssuesOverlay;
