( function () {
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

	module.exports = newPageIssueLearnMoreLink;
}() );
