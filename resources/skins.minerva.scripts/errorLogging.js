( function ( track, config, trackSubscribe, user ) {
	module.exports = function () {
		var suffix = user.isAnon() ? '.anon' : '.loggedin',
			COUNTER_NAME = 'counter.MediaWiki.minerva.WebClientError' + suffix;

		/**
		 * Count javascript error
		 */
		function countError() {
			track( COUNTER_NAME, 1 );
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
