module.exports = function () {
	var config = mw.config,
		shareIcon = require( './shareIcon.js' ),
		trackShare = require( './trackShare.js' ),
		features = config.get( 'wgMinervaFeatures', {} );

	/**
	 * Checks whether shareIcon is available for given user agent
	 *
	 * @return {boolean}
	 */
	function isShareAvailable() {
		return navigator.share !== undefined;
	}

	// check if browser supports share feature and the feature is enabled
	if ( isShareAvailable() && features.shareButton ) {
		// Because the page actions are floated to the right, their order in the
		// DOM is reversed in the display. The watchstar is last in the DOM and
		// left-most in the display. Since we want the download button to be to
		// the left of the watchstar, we put it after it in the DOM.
		$( '<li>' ).addClass( 'page-actions-menu__list-item' )
			.append( shareIcon( navigator ).$el )
			// eslint-disable-next-line no-jquery/no-global-selector
			.insertAfter( $( '#ca-watch' ).parent() );
		trackShare( 'shownShareButton' );
	}

};
