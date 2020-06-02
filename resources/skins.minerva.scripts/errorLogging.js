( function ( track, config, trackSubscribe, user ) {
	module.exports = function () {
		var suffix = user.isAnon() ? '.anon' : '.loggedin',
			COUNTER_NAME = 'counter.MediaWiki.minerva.WebClientError' + suffix;

		/**
		 * Count javascript errors, except for those associated with localStorage
		 * being full or unavailable.
		 *
		 * @param {string} topic name of the event being tracked
		 * @param {Object} data event payload
		 */
		function countError( topic, data ) {
			var isLocalStorage = data && data.source === 'store-localstorage-update';
			if ( !isLocalStorage ) {
				track( COUNTER_NAME, 1 );
			}
		}

		if ( config.get( 'wgMinervaCountErrors' ) ) {
			// track RL exceptions
			trackSubscribe( 'resourceloader.exception', countError );
			// setup the global error handler
			trackSubscribe( 'global.error', countError );
		}
	};
}(
	mw.track,
	mw.config,
	mw.trackSubscribe,
	mw.user
) );
