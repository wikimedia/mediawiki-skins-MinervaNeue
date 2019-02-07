( function ( M ) {

	var AB = M.require( 'skins.minerva.scripts/AB' ),
		util = M.require( 'mobile.startup' ).util,
		defaultConfig = {
			testName: 'WME.MinervaABTest',
			samplingRate: 0.5,
			sessionId: mw.user.generateRandomSessionId()
		};

	QUnit.module( 'Minerva AB-test' );

	QUnit.test( 'Bucketing test', function ( assert ) {
		var userBuckets = {
				unsampled: 0,
				control: 0,
				treatment: 0
			},
			maxUsers = 1000,
			bucketingTest,
			config,
			i;

		for ( i = 0; i < maxUsers; i++ ) {
			config = util.extend( {}, defaultConfig, {
				sessionId: mw.user.generateRandomSessionId()
			} );
			bucketingTest = new AB( config );
			if ( bucketingTest.isControl() ) {
				++userBuckets.control;
			} else if ( bucketingTest.isTreatment() ) {
				++userBuckets.treatment;
			} else if ( !bucketingTest.isSampled() ) {
				++userBuckets.unsampled;
			} else {
				throw new Error( 'Unknown bucket!' );
			}
		}

		assert.strictEqual(
			( userBuckets.unsampled / maxUsers > 0.4 ) &&
			( userBuckets.unsampled / maxUsers < 0.6 ),
			true, 'test unsampled group is about 50% (' + userBuckets.unsampled / 10 + '%)' );

		assert.strictEqual(
			( userBuckets.control / maxUsers > 0.2 ) &&
			( userBuckets.control / maxUsers < 0.3 ),
			true, 'test control group is about 25% (' + userBuckets.control / 10 + '%)' );

		assert.strictEqual(
			( userBuckets.treatment / maxUsers > 0.2 ) &&
			( userBuckets.treatment / maxUsers < 0.3 ),
			true, 'test new treatment group is about 25% (' + userBuckets.treatment / 10 + '%)' );
	} );

}( mw.mobileFrontend ) );
