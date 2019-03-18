( function ( M, track, user, config ) {
	/**
	 * Helper function to track share button usage
	 *
	 * @param {string} action - one of "shownShareButton", "clickShareButton", "SharedToApp"
	 */
	function trackShare( action ) {
		track( 'event.MobileWebShareButton', {
			pageTitle: config.get( 'wgTitle' ),
			namespaceId: config.get( 'wgNamespaceNumber' ),
			isAnon: user.isAnon(),
			action: action,
			pageToken: user.getPageviewToken()
		} );
	}

	M.define( 'skins.minerva.share/track', trackShare );
}( mw.mobileFrontend, mw.track, mw.user, mw.config ) );
