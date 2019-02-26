( function ( M, requestIdleCallback, track, config, trackSubscribe, user, experiments ) {
	requestIdleCallback( function () {
		/**
		 * Handle an error and log it if necessary
		 * @param {string} errorMessage to be logged
		 * @param {number} [lineNumber] of error
		 * @param {number} [columnNumber] of error
		 * @param {string} [errorUrl] to be logged
		 */
		function handleError( errorMessage, lineNumber, columnNumber, errorUrl ) {
			var suffix,
				errorSamplingRate = config.get( 'wgMinervaErrorLogSamplingRate', 0 ),
				sessionToken = user.sessionId(),
				EVENT_CLIENT_ERROR_LOG = 'wikimedia.event.WebClientError',
				page = M.getCurrentPage(),
				util = M.require( 'mobile.startup' ).util,
				errorExperiment = {
					name: 'WebClientError',
					enabled: errorSamplingRate > 0,
					buckets: {
						on: errorSamplingRate,
						off: 1 - errorSamplingRate
					}
				},
				isErrorLoggingEnabled = experiments.getBucket( errorExperiment, sessionToken ) === 'on',
				DEFAULT_ERROR_DATA = {
					sessionToken: sessionToken,
					skin: config.get( 'skin' ),
					wgVersion: config.get( 'wgVersion' ),
					mobileMode: config.get( 'wgMFMode', 'desktop' ),
					isAnon: user.isAnon(),
					revision: page.getRevisionId()
				};

			if ( isErrorLoggingEnabled ) {
				track( EVENT_CLIENT_ERROR_LOG,
					util.extend( {
						userUrl: window.location.href,
						errorUrl: errorUrl,
						errorMessage: errorMessage,
						// Due to concerns for the length of the stack trace and going over the
						// limit for URI length this is currently set to empty string.
						errorStackTrace: '',
						errorLineNumber: lineNumber || 0,
						errorColumnNumber: columnNumber || 0
					}, DEFAULT_ERROR_DATA )
				);
			}
			if ( config.get( 'wgMinervaCountErrors' ) ) {
				suffix = user.isAnon() ? '.anon' : '.loggedin';
				mw.track( 'counter.MediaWiki.minerva.WebClientError' + suffix, 1 );
			}
		}
		// track RL exceptions
		trackSubscribe( 'resourceloader.exception', function ( topic, data ) {
			var error = data.exception;
			handleError( error.message, error.lineNumber, error.columnNumber );
		} );
		// setup the global error handler
		trackSubscribe( 'global.error', function ( topic, error ) {
			handleError( error.errorMessage, error.lineNumber, error.columnNumber, error.url );
		} );
	} );
}(
	mw.mobileFrontend,
	mw.requestIdleCallback,
	mw.track,
	mw.config,
	mw.trackSubscribe,
	mw.user,
	mw.experiments
) );
