( function ( M ) {
	/**
	 * Creates a "read more" button with given text.
	 * @param {string} msg
	 * @return {JQuery}
	 */
	function newPageIssueLearnMoreLink( msg ) {
		return $( '<span>' )
			.addClass( 'ambox-learn-more' )
			.text( msg );
	}

	M.define( 'skins.minerva.scripts/page-issues/page/PageIssueLearnMoreLink', newPageIssueLearnMoreLink );
}( mw.mobileFrontend ) );
