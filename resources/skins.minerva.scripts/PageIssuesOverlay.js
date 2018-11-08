( function ( M, mwMsg ) {
	var
		Overlay = M.require( 'mobile.startup/Overlay' ),
		util = M.require( 'mobile.startup/util' ),
		KEYWORD_ALL_SECTIONS = 'all',
		NS_MAIN = 0,
		NS_TALK = 1,
		NS_CATEGORY = 14;

	/**
	 * Overlay for displaying page issues
	 * @class PageIssuesOverlay
	 * @extends Overlay
	 *
	 * @param {IssueSummary[]} issues list of page issue summaries for display.
	 * @param {string} section
	 * @param {number} namespaceID
	 */
	function PageIssuesOverlay( issues, section, namespaceID ) {
		var
			// Note only the main namespace is expected to make use of section issues, so the
			// heading will always be minerva-meta-data-issues-section-header regardless of
			// namespace.
			headingText = section === '0' || section === KEYWORD_ALL_SECTIONS ?
				getNamespaceHeadingText( namespaceID ) :
				mwMsg( 'minerva-meta-data-issues-section-header' );

		Overlay.call( this, {
			issues: issues,
			heading: '<strong>' + headingText + '</strong>'
		} );
	}

	OO.mfExtend( PageIssuesOverlay, Overlay, {
		/**
		 * @memberof PageIssuesOverlay
		 * @instance
		 */
		className: 'overlay overlay-issues',

		/**
		 * @memberof PageIssuesOverlay
		 * @instance
		 */
		templatePartials: util.extend( {}, Overlay.prototype.templatePartials, {
			content: mw.template.get( 'skins.minerva.scripts', 'PageIssuesOverlayContent.hogan' )
		} )
	} );

	/**
	 * Obtain a suitable heading for the issues overlay based on the namespace
	 * @param {number} namespaceID is the namespace to generate heading for
	 * @return {string} heading for overlay
	 */
	function getNamespaceHeadingText( namespaceID ) {
		switch ( namespaceID ) {
			case NS_CATEGORY:
				return mw.msg( 'mobile-frontend-meta-data-issues-categories' );
			case NS_TALK:
				return mw.msg( 'mobile-frontend-meta-data-issues-talk' );
			case NS_MAIN:
				return mw.msg( 'mobile-frontend-meta-data-issues' );
			default:
				return '';
		}
	}

	M.define( 'skins.minerva.scripts/PageIssuesOverlay', PageIssuesOverlay );
}( mw.mobileFrontend, mw.msg ) );
