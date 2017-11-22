( function ( M ) {
	var Skin = M.require( 'mobile.startup/Skin' ),
		Deferred = $.Deferred,
		DownloadIcon = M.require( 'skins.minerva.scripts/DownloadIcon' );

	QUnit.module( 'Minerva DownloadIcon', {
		setup: function () {
			this.skin = new Skin( {} );
		}
	} );

	QUnit.test( '#DownloadIcon (print after image download)', function ( assert ) {
		var icon = new DownloadIcon( this.skin ),
			d = Deferred(),
			spy = this.sandbox.stub( window, 'print' );

		this.sandbox.stub( this.skin, 'loadImagesList' ).returns( d.resolve() );

		icon.onClick();
		d.then( function () {
			assert.ok( spy.calledOnce, 'Print occurred.' );
		} );

		return d;
	} );

	QUnit.test( '#DownloadIcon (print via timeout)', function ( assert ) {
		var icon = new DownloadIcon( this.skin ),
			d = Deferred(),
			spy = this.sandbox.stub( window, 'print' );

		this.sandbox.stub( this.skin, 'loadImagesList' ).returns( d );

		window.setTimeout( function () {
			d.resolve();
		}, 3400 );

		icon.onClick();
		d.then( function () {
			assert.ok( spy.calledOnce,
				'Print was called once despite loadImagesList resolving after MAX_PRINT_TIMEOUT' );
		} );

		return d;
	} );

	QUnit.test( '#DownloadIcon (multiple clicks)', function ( assert ) {
		var icon = new DownloadIcon( this.skin ),
			d = Deferred(),
			spy = this.sandbox.stub( window, 'print' );

		this.sandbox.stub( this.skin, 'loadImagesList' ).returns( d );

		window.setTimeout( function () {
			d.resolve();
		}, 3400 );

		icon.onClick();
		icon.onClick();
		d.then( function () {
			assert.ok( spy.calledOnce,
				'Print was called once despite multiple clicks' );
		} );

		return d;
	} );
}( mw.mobileFrontend ) );
