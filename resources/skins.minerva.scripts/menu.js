var MainMenu = require( './menu/MainMenu.js' ),
	mainMenu = createMainMenu();

/**
 * N.B. that the activator - the UI element that the user must click in order to open the main
 * menu - is always `.header .main-menu-button`.
 *
 * @return {MainMenu}
 *
 * @ignore
 */
function createMainMenu() {
	return new MainMenu( '.header .main-menu-button' );
}

/**
 * Wire up the main menu
 */
function init() {
	/**
	 * Close navigation if skin is tapped
	 * @private
	 */
	function onSkinClick() {
		mainMenu.closeNavigationDrawers();
	}

	// eslint-disable-next-line no-jquery/no-global-selector
	$( '.mw-mf-page-center__mask' ).on( 'click', onSkinClick );

	// See I09c27a084100b223662f84de6cbe01bebe1fe774
	// will trigger every time the Echo notification is opened or closed.
	// This controls the drawer like behaviour of notifications
	// on tablet in mobile mode.
	mw.hook( 'echo.mobile' ).add( function ( isOpen ) {
		if ( isOpen ) {
			mainMenu.openNavigationDrawer( 'secondary' );
		} else {
			mainMenu.closeNavigationDrawers();
		}
	} );
}

module.exports = {
	mainMenu: mainMenu,
	init: init
};
