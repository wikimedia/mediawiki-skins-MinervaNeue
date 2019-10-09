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
	var options = mw.config.get( 'wgMinervaMenuData', {} );

	options.activator = '.header .main-menu-button';

	return new MainMenu( options );
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
	function onSkinClick() {
		mainMenu.closeNavigationDrawers();
	}
	// FIXME: This is for cached HTML and can be removed shortly.
	// Ref: I3892afb5ed3df628e2845043cf3bbc22a9928921.
	// eslint-disable-next-line no-jquery/no-global-selector
	if ( !$( '#mw-mf-page-left' ).find( '.menu' ).length ) {
		mainMenu.appendTo( '#mw-mf-page-left' );
	}
	// eslint-disable-next-line no-jquery/no-global-selector
	if ( $( '.mw-mf-page-center__mask' ).length === 0 ) {
		$( '<a>' ).addClass( 'mw-mf-page-center__mask transparent-shield' ).prependTo( '#mw-mf-page-center' );
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
