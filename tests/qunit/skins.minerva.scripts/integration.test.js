/*!
 * Minerva integration tests.
 *
 * This should only be used to test APIs that Minerva depends on to work.
 * For unit tests please see tests/jest.
 */
QUnit.module( 'Minerva (integration)', function () {
	QUnit.test( '[T356653] Client preferences: Check assumptions about the cookie it writes to', function ( assert ) {
		mw.cookie.set( 'mwclientpreferences', '' );
		this.sandbox.stub( mw.user, 'isAnon' ).returns( true );
		document.documentElement.setAttribute( 'class', 'skin-night-mode-clientpref-0' );
		mw.user.clientPrefs.set( 'skin-night-mode', '1' );
		assert.strictEqual(
			mw.cookie.get( 'mwclientpreferences' ),
			'skin-night-mode-clientpref-1'
		);
	} );
} );
