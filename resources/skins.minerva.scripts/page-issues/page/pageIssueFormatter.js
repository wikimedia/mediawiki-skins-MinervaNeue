( function ( M ) {
	var
		newPageIssueLink = M.require( 'skins.minerva.scripts/page-issues/page/PageIssueLink' ),
		newPageIssueLearnMoreLink = M.require( 'skins.minerva.scripts/page-issues/page/PageIssueLearnMoreLink' );

	/**
	 * Modifies the `issue` DOM to create a banner designed for single / multiple issue templates,
	 * and handles event-binding for that issues overlay.
	 *
	 * @param {IssueSummary} issue
	 * @param {string} msg
	 * @param {string} overlayUrl
	 * @param {Object} overlayManager
	 * @param {boolean} [multiple]
	 */
	function insertPageIssueBanner( issue, msg, overlayUrl, overlayManager, multiple ) {
		var $learnMoreEl = newPageIssueLearnMoreLink( msg ),
			$issueContainer = multiple ?
				issue.$el.parents( '.mbox-text-span, .mbox-text-div' ) :
				issue.$el.find( '.mbox-text' ),
			$clickContainer = multiple ? issue.$el.parents( '.mbox-text' ) : issue.$el;

		$issueContainer.prepend( issue.iconString );
		$issueContainer.prepend( $learnMoreEl );

		$clickContainer.on( 'click', function () {
			overlayManager.router.navigate( overlayUrl );
			return false;
		} );
	}

	/**
	 * Modifies the page DOM to insert a page-issue notice below the title of the page,
	 * containing a link with a message like "this page has issues".
	 * Used on talk & category namespaces, or when page-issue banners have been disabled.s
	 *
	 * @param {string} labelText
	 * @param {string} section
	 */
	function insertPageIssueNotice( labelText, section ) {
		var $link = newPageIssueLink( labelText );
		$link.attr( 'href', '#/issues/' + section );
		// eslint-disable-next-line no-jquery/no-global-selector
		$link.insertAfter( $( 'h1#section_0' ) );
	}

	M.define( 'skins.minerva.scripts/page-issues/page/pageIssueFormatter', {
		insertPageIssueBanner: insertPageIssueBanner,
		insertPageIssueNotice: insertPageIssueNotice
	} );
}( mw.mobileFrontend ) );
