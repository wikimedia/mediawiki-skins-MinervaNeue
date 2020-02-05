// FIXME: make this an object with a constructor to facilitate testing
// (see https://bugzilla.wikimedia.org/show_bug.cgi?id=44264)
/**
 * mobileFrontend namespace
 * @class mw.mobileFrontend
 * @singleton
 */
module.exports = function () {
	// eslint-disable-next-line no-restricted-properties
	var M = mw.mobileFrontend,
		mobile = M.require( 'mobile.startup' ),
		menus = require( './menu.js' );

	// loads lazy loading images
	mobile.Skin.getSingleton();

	// setup main menu
	menus.init();

	( function ( wgRedirectedFrom ) {
		// If the user has been redirected, then show them a toast message (see
		// https://phabricator.wikimedia.org/T146596).

		var redirectedFrom, $msg, title;

		if ( wgRedirectedFrom === null ) {
			return;
		}

		redirectedFrom = mw.Title.newFromText( wgRedirectedFrom );

		if ( redirectedFrom ) {
			// mw.Title.getPrefixedText includes the human-readable namespace prefix.
			title = redirectedFrom.getPrefixedText();
			$msg = $( '<div>' ).html(
				mw.message( 'mobile-frontend-redirected-from', title ).parse()
			);
			$msg.find( 'a' ).attr( 'href', mw.util.getUrl( title, { redirect: 'no' } ) );
			mw.notify( $msg );
		}
	}( mw.config.get( 'wgRedirectedFrom' ) ) );
	/* eslint-enable no-console */
};
