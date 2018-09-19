( function ( M ) {

	var AB = M.require( 'skins.minerva.scripts/AB' ),
		util = M.require( 'mobile.startup/util' ),
		defaultConfig = {
			testName: 'WME.MinervaABTest',
			samplingRate: 0.5,
			sessionId: mw.user.generateRandomSessionId()
		};

	QUnit.module( 'Minerva AB-test' );

	QUnit.test( 'Bucketing test', function ( assert ) {
		var userBuckets = {
				control: 0,
				A: 0,
				B: 0
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
			if ( bucketingTest.isA() ) {
				++userBuckets.A;
			} else if ( bucketingTest.isB() ) {
				++userBuckets.B;
			} else if ( !bucketingTest.isEnabled() ) {
				++userBuckets.control;
			} else {
				throw new Error( 'Unknown bucket!' );
			}
		}

		assert.strictEqual(
			( userBuckets.control / maxUsers > 0.4 ) &&
			( userBuckets.control / maxUsers < 0.6 ),
			true, 'test control group is about 50% (' + userBuckets.control / 10 + '%)' );

		assert.strictEqual(
			( userBuckets.A / maxUsers > 0.2 ) &&
			( userBuckets.A / maxUsers < 0.3 ),
			true, 'test group A is about 25% (' + userBuckets.A / 10 + '%)' );

		assert.strictEqual(
			( userBuckets.B / maxUsers > 0.2 ) &&
			( userBuckets.B / maxUsers < 0.3 ),
			true, 'test group B is about 25% (' + userBuckets.B / 10 + '%)' );
	} );

}( mw.mobileFrontend ) );
