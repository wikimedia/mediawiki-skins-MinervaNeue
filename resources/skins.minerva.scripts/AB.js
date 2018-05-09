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
	 * @param {string} testName name of the AB-test.
	 * @param {number} samplingRate sampling rate for the AB-test.
	 * @param {number} sessionId session ID for user bucketing.
	 * @constructor
	 */
	function AB( testName, samplingRate, sessionId ) {

		var CONTROL_BUCKET = 'control',
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
		 * Starts the AB-test and enters the user into the Reading Depth test.
 		 */
		function startABTest() {
			// See: https://gerrit.wikimedia.org/r/#/c/mediawiki/extensions/WikimediaEvents/+/437686/
			mw.track( 'wikimedia.event.ReadingDepthSchema.enable' );
		}

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
				startABTest();
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
