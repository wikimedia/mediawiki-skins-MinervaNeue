/**
 * This module is loaded by resources/skins.minerva.scripts/menu/MainMenu.js
 * inside the Minerva skin. It should be moved to Minerva at our earliest possible
 * convenience.
 */
mw.loader.using( [
	'ext.eventLogging.subscriber'
] ).then( function () {
	var M = mw.mobileFrontend,
		user = mw.user,
		editCount = mw.config.get( 'wgUserEditCount' ),
		// Schema provided by ext.eventLogging.subscriber class
		Schema = mw.eventLog.Schema, // resource-modules-disable-line
		context = M.require( 'mobile.startup' ).context,
		/**
		 * MobileWebMainMenuClickTracking schema
		 * https://meta.wikimedia.org/wiki/Schema:MobileWebMainMenuClickTracking
		 *
		 * @class MobileWebMainMenuClickTracking
		 * @singleton
		 */
		schemaMobileWebMainMenuClickTracking = new Schema(
			'MobileWebMainMenuClickTracking',
			mw.config.get( 'wgMinervaSchemaMainMenuClickTrackingSampleRate' ),
			/**
			 * @property {Object} defaults Default options hash.
			 * @property {string} defaults.mobileMode whether user is in stable or beta
			 * @property {string} [defaults.username] Username if the user is logged in,
			 *  otherwise - undefined.
			 *  Assigning undefined will make event logger omit this property when sending
			 *  the data to a server. According to the schema username is optional.
			 * @property {number} [defaults.userEditCount] The number of edits the user has made
			 *  if the user is logged in, otherwise - undefined. Assigning undefined will make event
			 *  logger omit this property when sending the data to a server. According to the schema
			 *  userEditCount is optional.
			 */
			{
				mode: context.getMode() || 'desktop',
				username: user.getName() || undefined,
				// FIXME: Use edit bucket here (T210106)
				userEditCount: typeof editCount === 'number' ? editCount : undefined
			}
		);

	mw.trackSubscribe( 'minerva.schemaMobileWebMainMenuClickTracking', function ( topic, data ) {
		schemaMobileWebMainMenuClickTracking.log( data );
	} );
} );
