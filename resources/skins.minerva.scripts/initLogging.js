// This initialises EventLogging for main menu and some prominent links in the UI.
// This code should only be loaded on the Minerva skin, it does not apply to other skins.
( function ( M, $ ) {
	var mainMenu = M.require( 'skins.minerva.scripts.top/mainMenu' );

	/**
	 * Enable WikimediaEvents including ReadingDepth schema
	 */
	function subscribeToWikimediaEvents() {
		mw.loader.using( 'ext.wikimediaEvents' );
	}

	$( function () {
		mainMenu.enableLogging();
	} );

	M.define( 'skins.minerva.scripts/subscribeToWikimediaEvents', subscribeToWikimediaEvents );
}( mw.mobileFrontend, jQuery ) );
