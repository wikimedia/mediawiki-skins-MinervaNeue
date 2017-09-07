// FIXME: make this an object with a constructor to facilitate testing
// (see https://bugzilla.wikimedia.org/show_bug.cgi?id=44264)
/**
 * mobileFrontend namespace
 * @class mw.mobileFrontend
 * @singleton
 */
( function ( M ) {
	var skin = M.require( 'mobile.init/skin' ),
		mainMenu = M.require( 'skins.minerva.scripts.top/mainMenu' ),
		toast = M.require( 'mobile.startup/toast' );

	// Proxy to MobileFrontend defined skin
	M.define( 'skins.minerva.scripts/skin', skin );

	/**
	 * Close navigation if skin is tapped
	 * @param {jQuery.Event} ev
	 * @private
	 */
	function onSkinClick( ev ) {
		var $target = this.$( ev.target );

		// Make sure the menu is open and we are not clicking on the menu button
		if (
			mainMenu &&
			mainMenu.isOpen() &&
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

		var redirectedFrom;

		if ( wgRedirectedFrom === null ) {
			return;
		}

		redirectedFrom = mw.Title.newFromText( wgRedirectedFrom );

		if ( redirectedFrom ) {

			// mw.Title.getPrefixedText includes the human-readable namespace prefix.
			toast.show( mw.msg(
				'mobile-frontend-redirected-from',
				redirectedFrom.getPrefixedText()
			) );
		}
	}( mw.config.get( 'wgRedirectedFrom' ) ) );

	// Recruit volunteers through the console (note console.log may not be a function so check via apply)
	/* eslint-disable no-console */
	if ( window.console && window.console.log && window.console.log.apply &&
			mw.config.get( 'wgMFEnableJSConsoleRecruitment' ) ) {
		console.log( mw.msg( 'mobile-frontend-console-recruit' ) );
	}
	/* eslint-enable no-console */
}( mw.mobileFrontend ) );
