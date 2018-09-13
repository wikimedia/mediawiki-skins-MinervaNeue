/*
 * Bucketing wrapper for creating AB-tests.
 *
 * Given a test name, sampling rate, and session ID, provides a class that buckets users into
 * predefined bucket ("control", "A", "B") and starts an AB-test.
 */
( function ( M, mwExperiments ) {
	var bucket = {
		CONTROL: 'control',
		A: 'A',
		B: 'B'
	};

	/**
	 * Buckets users based on params and exposes an `isEnabled` and `getBucket` method.
	 * @param {Object}   config Configuration object for AB test.
	 * @param {string}   config.testName
	 * @param {number}   config.samplingRate Sampling rate for the AB-test.
	 * @param {number}   config.sessionId Session ID for user bucketing.
	 * @constructor
	 */
	function AB( config ) {
		var
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
		 * Gets the users AB-test bucket.
		 *
		 * A boolean instead of an enum is usually a code smell. However, the nature of A/B testing
		 * is to compare an A group's performance to a B group's so a boolean seems natural, even
		 * in the long term, and preferable to showing bucketing encoding ("A", "B", "control") to
		 * callers which is necessary if getBucket(). The downside is that now two functions exist
		 * where one would suffice.
		 *
		 * @return {string} AB-test bucket, bucket.CONTROL_BUCKET by default, bucket.A or bucket.B
		 *                  buckets otherwise.
		 */
		function getBucket() {
			return mwExperiments.getBucket( test, sessionId );
		}

		function isA() {
			return getBucket() === bucket.A;
		}

		function isB() {
			return getBucket() === bucket.B;
		}

		/**
		 * Checks whether or not a user is in the AB-test,
		 * @return {boolean}
		 */
		function isEnabled() {
			return getBucket() !== bucket.CONTROL; // I.e., `isA() || isB()`;
		}

		return {
			isA: isA,
			isB: isB,
			isEnabled: isEnabled
		};
	}

	M.define( 'skins.minerva.scripts/AB', AB );
}( mw.mobileFrontend, mw.experiments ) );
