( function ( track, config, trackSubscribe, user ) {
	module.exports = function () {
		var suffix = user.isAnon() ? '.anon' : '.loggedin',
			// we will keep track of errors counted, so that we don't overcount problematic
			// browsers/users which are generating multiple issues. See MAX_ERRORS.
			reportedInThisSession = 0,
			// Very conservative for now.
			// After this many errors are tracked, no more will be logged.
			MAX_ERRORS = 5,
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
			// Some errors, for example the Leaflet "Set map center and zoom first"
			// error (T257872) can trigger a high volume of client side error reports to the server.
			// At time of writing 1 IP logged 245 errors in the same session and page and another
			// logged 3,029 errors across 2 pages. To filter these kind of errors out, set a cap
			// on the amount of errors that can be logged in a single session.
			if ( !isLocalStorage && reportedInThisSession < MAX_ERRORS ) {
				track( COUNTER_NAME, 1 );
			}
			reportedInThisSession++;
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
