var MainMenu = require( './menu/MainMenu.js' ),
	mainMenu = createMainMenu();

/**
 * Creates an instance of the `MainMenu`, using the `wgMinervaMenuData` for configuration.
 *
 * N.B. that the activator - the UI element that the user must click in order to open the main
 * menu - is always `.header .main-menu-button`.
 *
 * @return {MainMenu}
 *
 * @ignore
 */
function createMainMenu() {
	return new MainMenu( {
		activator: '.header .main-menu-button'
	} );
}

/**
 * Wire up the main menu
 */
function init() {
	mainMenu.registerClickEvents();
	/**
	 * Close navigation if skin is tapped
	 * @param {JQuery.Event} ev
	 * @private
	 */
	function onSkinClick( ev ) {
		mainMenu.closeNavigationDrawers();
		ev.preventDefault();
	}
	// FIXME: This is for cached HTML and can be removed shortly.
	// Ref: I3892afb5ed3df628e2845043cf3bbc22a9928921.
	// eslint-disable-next-line no-jquery/no-global-selector
	if ( $( '.mw-mf-page-center__mask' ).length === 0 ) {
		$( '<a>' ).addClass( 'mw-mf-page-center__mask' ).prependTo( '#mw-mf-page-center' );
	}
	// eslint-disable-next-line no-jquery/no-global-selector
	$( '.mw-mf-page-center__mask' ).on( 'click', onSkinClick );
}

module.exports = {
	mainMenu: mainMenu,
	init: init
};
