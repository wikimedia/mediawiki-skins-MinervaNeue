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

module.exports = {
	// Version number allows breaking changes to be detected by other extensions
	VERSION: 1
};
