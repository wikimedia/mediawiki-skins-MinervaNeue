// This initialises EventLogging for main menu and some prominent links in the UI.
// This code should only be loaded on the Minerva skin, it does not apply to other skins.
( function ( M ) {
	var mainMenu = M.require( 'skins.minerva.scripts/mainMenu' );

	$( function () {
		mainMenu.enableLogging();
	} );
}( mw.mobileFrontend ) );
