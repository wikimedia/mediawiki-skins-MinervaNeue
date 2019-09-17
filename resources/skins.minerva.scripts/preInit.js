// FIXME: make this an object with a constructor to facilitate testing
// (see https://bugzilla.wikimedia.org/show_bug.cgi?id=44264)
/**
 * mobileFrontend namespace
 * @class mw.mobileFrontend
 * @singleton
 */
module.exports = function () {
	var M = mw.mobileFrontend,
		mobile = M.require( 'mobile.startup' ),
		skin = mobile.Skin.getSingleton(),
		mainMenu = require( './menu.js' );

	/**
	 * Close navigation if skin is tapped
	 * @param {JQuery.Event} ev
	 * @private
	 */
	function onSkinClick( ev ) {
		var $target = $( ev.target );

		// Make sure the menu is open and we are not clicking on the menu button
		if (
			mainMenu &&
			mainMenu.isOpen() &&
			// eslint-disable-next-line no-jquery/no-class-state
			!$target.hasClass( 'main-menu-button' )
		) {
			mainMenu.closeNavigationDrawers();
			ev.preventDefault();
		}
	}
	skin.on( 'click', onSkinClick.bind( skin ) );

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
