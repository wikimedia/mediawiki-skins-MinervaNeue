// This initialises EventLogging for main menu and some prominent links in the UI.
// This code should only be loaded on the Minerva skin, it does not apply to other skins.
// @deprecated and to be removed the moment that T220016 is live.
var mainMenu = require( './menu.js' ),
	logging = require( './menu/schema.js' );

module.exports = function () {
	mw.loader.using( [
		'ext.eventLogging'
	] ).then( function () {
		logging();
		mainMenu.enableLogging();
	} );
};
