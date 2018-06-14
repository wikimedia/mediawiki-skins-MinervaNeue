( function ( M ) {

	var AB = M.require( 'skins.minerva.scripts/AB' ),
		aBName = 'WME.MinervaABTest',
		samplingRate = 0.5;

	QUnit.module( 'Minerva AB-test' );

	QUnit.test( 'Bucketing test', function ( assert ) {
		var userBuckets = {
				control: 0,
				A: 0,
				B: 0
			},
			maxUsers = 1000,
			bucketingTest,
			i;

		for ( i = 0; i < maxUsers; i++ ) {
			bucketingTest = new AB( aBName, samplingRate, mw.user.generateRandomSessionId() );
			userBuckets[ bucketingTest.getBucket() ] += 1;
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
