/**
 * This setups the Minerva skin.
 * It should run without errors even if MobileFrontend is not installed.
 */
var ms = require( 'mobile.startup' );

function init() {
	var permissions = mw.config.get( 'wgMinervaPermissions' ) || {},
		// eslint-disable-next-line no-jquery/no-global-selector
		$watch = $( '#page-actions-watch' );

	if ( permissions.watch && !mw.user.isAnon() ) {
		require( './watchstar.js' )( $watch );
	}

	// Setup Minerva with MobileFrontend
	if ( ms && !ms.stub ) {
		require( './initMobile.js' )();
	} else {
		// MOBILEFRONTEND IS NOT INSTALLED.
		// setup search for desktop Minerva at mobile resolution without MobileFrontend.
		require( './searchSuggestReveal.js' )();
	}
}

init();

// FIXME: Update references to old icons in cached HTML
// Can be removed about 2-3 weeks after I0f929f2f3b11362e02f0d02f57b90b34b5c93d24 is live.
// This only updates icons which have changed name that show for logged out users.
// Not necessary for watchstar->unStar or login->logIn
/* eslint-disable no-jquery/no-global-selector */
$( '.mw-ui-icon-random' ).addClass( 'mw-ui-icon-die' );
$( '.mw-ui-icon-nearby' ).addClass( 'mw-ui-icon-mapPin' );
$( '.mw-ui-icon-login' ).addClass( 'mw-ui-icon-logIn' );
/* eslint-enable no-jquery/no-global-selector */

module.exports = {
	// Version number allows breaking changes to be detected by other extensions
	VERSION: 1
};
