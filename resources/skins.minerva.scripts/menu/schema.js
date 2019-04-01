mw.loader.using( [
	'ext.eventLogging.subscriber'
] ).then( function () {
	var M = mw.mobileFrontend,
		user = mw.user,
		editCount = mw.config.get( 'wgUserEditCount' ),
		// Need to make amc default to false because it will not exist in mw.config
		// if using desktop Minerva or if MobileFrontend extension is not installed.
		amc = mw.config.get( 'wgMFAmc', false ),
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
			 * @property {string} defaults.mode whether user is in stable, beta, or desktop
			 * @property {boolean} defaults.amc whether or not the user has advanced
			 * contributions mode enabled (true) or disabled (false)
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
				amc: amc,
				username: user.getName() || undefined,
				// FIXME: Use edit bucket here (T210106)
				userEditCount: typeof editCount === 'number' ? editCount : undefined
			}
		);

	mw.trackSubscribe( 'minerva.schemaMobileWebMainMenuClickTracking', function ( topic, data ) {
		if ( amc ) {
			// T218627: Sampling rate should be 100% if user has amc enabled
			schemaMobileWebMainMenuClickTracking.log( data, 1 );
			return;
		}

		schemaMobileWebMainMenuClickTracking.log( data );
	} );
} );
