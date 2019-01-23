( function ( M ) {
	/**
	 * Create a link element that opens the issues overlay.
	 *
	 * @param {string} labelText The text value of the element
	 * @return {JQuery}
	 */
	function newPageIssueLink( labelText ) {
		return $( '<a>' ).addClass( 'cleanup mw-mf-cleanup' ).text( labelText );
	}

	M.define( 'skins.minerva.scripts/page-issues/page/PageIssueLink', newPageIssueLink );
}( mw.mobileFrontend ) );
