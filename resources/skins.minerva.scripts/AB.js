/*
 * Bucketing wrapper for creating AB-tests.
 *
 * Given a test name, sampling rate, and session ID, provides a class
 * that buckets users into predefined bucket ("control", "A", "B") and
 * starts an AB-test.
 */

( function ( mw, M, mwExperiments ) {

	/**
	 * Buckets users based on params and exposes an `isEnabled` and `getBucket` method.
	 *
	 * @param {Object}   config configuration object for AB test.
	 * @param {string}   config.testName
	 * @param {number}   config.samplingRate sampling rate for the AB-test.
	 * @param {number}   config.sessionId session ID for user bucketing
	 * @param {function} [config.onABStart] function that triggers event-logging when user is either
	 *                                      in bucket A or B.
	 * @constructor
	 */
	function AB( config ) {

		var CONTROL_BUCKET = 'control',
			testName = config.testName,
			samplingRate = config.samplingRate,
			sessionId = config.sessionId,
			onABStart = config.onABStart || function () {},
			test = {
				name: testName,
				enabled: !!samplingRate,
				buckets: {
					control: 1 - samplingRate,
					A: samplingRate / 2,
					B: samplingRate / 2
				}
			};

		/**
		 * Gets the users AB-test bucket
		 *
		 * @return {string} AB-test bucket, CONTROL_BUCKET by default, "A" or "B" buckets otherwise.
		 */
		function getBucket() {
			return mwExperiments.getBucket( test, sessionId );
		}

		/**
		 * Checks whether or not a user is in the AB-test,
		 *
		 * @return {boolean}
		 */
		function isEnabled() {
			return getBucket() !== CONTROL_BUCKET;
		}

		/**
		 * Initiates the AB-test.
		 *
		 * return {void}
		 */
		function init() {
			if ( isEnabled() ) {
				onABStart( getBucket() );
			}
		}

		init();

		return {
			getBucket: getBucket,
			isEnabled: isEnabled
		};
	}

	M.define( 'skins.minerva.scripts/AB', AB );

}( mw, mw.mobileFrontend, mw.experiments ) );
