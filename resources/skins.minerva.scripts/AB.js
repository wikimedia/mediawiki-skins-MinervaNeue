/*
 * Bucketing wrapper for creating AB-tests.
 *
 * Given a test name, sampling rate, and session ID, provides a class that buckets users into
 * predefined bucket ("control", "A", "B") and starts an AB-test.
 */
( function ( M, mwExperiments ) {
	/**
	 * Buckets users based on params and exposes an `isEnabled` and `getBucket` method.
	 * @param {Object}   config Configuration object for AB test.
	 * @param {string}   config.testName
	 * @param {number}   config.samplingRate Sampling rate for the AB-test.
	 * @param {number}   config.sessionId Session ID for user bucketing.
	 * @constructor
	 */
	function AB( config ) {
		var CONTROL_BUCKET = 'control',
			testName = config.testName,
			samplingRate = config.samplingRate,
			sessionId = config.sessionId,
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
		 * @return {string} AB-test bucket, CONTROL_BUCKET by default, "A" or "B" buckets otherwise.
		 */
		function getBucket() {
			return mwExperiments.getBucket( test, sessionId );
		}

		/**
		 * Checks whether or not a user is in the AB-test,
		 * @return {boolean}
		 */
		function isEnabled() {
			return getBucket() !== CONTROL_BUCKET;
		}

		return {
			getBucket: getBucket,
			isEnabled: isEnabled
		};
	}

	M.define( 'skins.minerva.scripts/AB', AB );
}( mw.mobileFrontend, mw.experiments ) );
