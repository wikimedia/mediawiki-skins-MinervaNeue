var MainMenu = require( './menu/MainMenu.js' ),
	util = mw.mobileFrontend.require( 'mobile.startup' ).util,
	mainMenu = createMainMenu();

/**
 * Update body tag with appropriate classes for a closed drawer.
 */
function onClose() {
	$( document.body ).removeClass(
		[ 'navigation-enabled', 'secondary-navigation-enabled',
			'primary-navigation-enabled' ].join( ' ' )
	);
}

/**
 * Update body tag with appropriate classes for an open drawer.
 * @param {string} drawerType A name that identifies the navigation drawer that
 *  should be opened. Either primary or secondary.
 */
function onOpen( drawerType ) {
	// FIXME: We should be moving away from applying classes to the body
	$( document.body ).addClass(
		[ 'navigation-enabled', drawerType + '-navigation-enabled' ].join( ' ' )
	);
}

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

	return new MainMenu(
		util.extend( options, {
			onClose: onClose,
			onOpen: function () {
				onOpen( 'primary' );
			}
		} )
	);
}

$( function () {
	// eslint-disable-next-line no-jquery/no-global-selector
	if ( !$( '#mw-mf-page-left' ).find( '.menu' ).length ) {
		// Now we have a main menu button register it.
		mainMenu.registerClickEvents();
		mainMenu.appendTo( '#mw-mf-page-left' );
	}
} );

module.exports = mainMenu;
