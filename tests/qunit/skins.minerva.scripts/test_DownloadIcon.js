( function ( M ) {
	var VALID_UA = 'Mozilla/5.0 (Linux; Android 5.1.1; Nexus 6 Build/LYZ28E) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Mobile Safari/537.36',
		VALID_SUPPORTED_NAMESPACES = [ 0 ],
		Skin = M.require( 'mobile.startup/Skin' ),
		Deferred = $.Deferred,
		windowChrome = { chrome: true },
		windowNotChrome = {},
		DownloadIcon = M.require( 'skins.minerva.scripts/DownloadIcon' ),
		browser = M.require( 'mobile.startup/Browser' ).getSingleton(),
		Page = M.require( 'mobile.startup/Page' );

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

	QUnit.module( 'Minerva DownloadIcon.isAvailable()', {
		setup: function () {
			this.skin = new Skin( {
				page: new Page( {
					id: 0,
					title: 'Test',
					isMainPage: false
				} )
			} );
		}
	} );

	QUnit.test( 'isAvailable() handles properly correct namespace', function ( assert ) {
		var icon = new DownloadIcon( this.skin, VALID_SUPPORTED_NAMESPACES, windowChrome );
		assert.ok( icon.isAvailable( VALID_UA ) );
	} );

	QUnit.test( 'isAvailable() handles properly not supported namespace', function ( assert ) {
		var icon = new DownloadIcon( this.skin, [ 9999 ], windowChrome );
		assert.notOk( icon.isAvailable( VALID_UA ) );
	} );

	QUnit.test( 'isAvailable() handles properly main page', function ( assert ) {
		var icon;
		this.skin.page = new Page( {
			id: 0,
			title: 'Test',
			isMainPage: true
		} );
		icon = new DownloadIcon( this.skin, VALID_SUPPORTED_NAMESPACES, windowChrome );
		assert.notOk( icon.isAvailable( VALID_UA ) );
	} );

	QUnit.test( 'isAvailable() returns false for iOS', function ( assert ) {
		var icon;
		this.sandbox.stub( browser, 'isIos' ).returns( true );
		icon = new DownloadIcon( this.skin, VALID_SUPPORTED_NAMESPACES, windowChrome );
		assert.notOk( icon.isAvailable( VALID_UA ) );
	} );

	QUnit.test( 'isAvailable() uses window.chrome to filter certain chrome-like browsers', function ( assert ) {
		var icon = new DownloadIcon( this.skin, VALID_SUPPORTED_NAMESPACES, windowNotChrome );
		// Dolphin
		assert.notOk( icon.isAvailable( ' Mozilla/5.0 (Linux; Android 7.0; SM-G950U1 Build/NRD90M; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/61.0.3163.98 Mobile Safari/537.36' ) );
		// Opera
		assert.notOk( icon.isAvailable( 'Mozilla/5.0 (Linux; Android 7.0; SM-G950U1 Build/NRD90M) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.116 Mobile Safari/537.36 OPR/44.1.2246.123029' ) );
		// Maxthon
		assert.notOk( icon.isAvailable( 'Mozilla/5.0 (Linux; Android 7.0; SM-G950U1 Build/NRD90M; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/63.0.3239.111 Mobile Safari/537.36 MxBrowser/4.5.10.1300' ) );
	} );

	QUnit.test( 'isAvailable() handles properly non-chrome browsers', function ( assert ) {
		var icon = new DownloadIcon( this.skin, VALID_SUPPORTED_NAMESPACES, windowChrome );
		// IPhone 6 Safari
		assert.notOk( icon.isAvailable( 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_0_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13A405 Safari/601.1' ) );
		// Nokia Lumia 930 Windows Phone 8.1
		assert.notOk( icon.isAvailable( 'Mozilla/5.0 (Mobile; Windows Phone 8.1; Android 4.0; ARM; Trident/7.0; Touch; rv:11.0; IEMobile/11.0; Microsoft; Virtual) like iPhone OS 7_0_3 Mac OS X AppleWebKit/537 (KHTML, like Gecko) Mobile Safari/537' ) );
		// Firefox @ Ubuntu
		assert.notOk( icon.isAvailable( 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:56.0) Gecko/20100101 Firefox/56.0' ) );
	} );

	QUnit.test( 'isAvailable() handles properly non-chrome browsers', function ( assert ) {
		var icon = new DownloadIcon( this.skin, VALID_SUPPORTED_NAMESPACES, windowNotChrome );
		// IPhone 6 Safari
		assert.notOk( icon.isAvailable( 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_0_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13A405 Safari/601.1' ) );
		// Nokia Lumia 930 Windows Phone 8.1
		assert.notOk( icon.isAvailable( 'Mozilla/5.0 (Mobile; Windows Phone 8.1; Android 4.0; ARM; Trident/7.0; Touch; rv:11.0; IEMobile/11.0; Microsoft; Virtual) like iPhone OS 7_0_3 Mac OS X AppleWebKit/537 (KHTML, like Gecko) Mobile Safari/537' ) );
		// Firefox @ Ubuntu
		assert.notOk( icon.isAvailable( 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:56.0) Gecko/20100101 Firefox/56.0' ) );
	} );

	QUnit.test( 'isAvailable() handles properly old devices', function ( assert ) {
		var icon = new DownloadIcon( this.skin, VALID_SUPPORTED_NAMESPACES );
		// Samsung Galaxy S5, Android 4.4, Chrome 28
		assert.notOk( icon.isAvailable( 'Mozilla/5.0 (Linux; Android 4.4.2; en-us; SAMSUNG SM-G900F Build/KOT49H) AppleWebKit/537.36 (KHTML, like Gecko) Version/1.6 Chrome/28.0.1500.94 Mobile Safari/537.36' ) );
		// Samsung Galaxyu S1, Android 4.2.2 Cyanogenmod + built in Samsung Browser
		assert.notOk( icon.isAvailable( 'Mozilla/5.0 (Linux; U; Android 4.2.2; en-ca; GT-I9000 Build/JDQ39E) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30 CyanogenMod/10.1.0/galaxysmtd' ) );
		// Samsung Galaxy S3
		assert.notOk( icon.isAvailable( 'Mozilla/5.0 (Linux; Android 4.3; GT-I9300 Build/JSS15J) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.84 Mobile Safari/537.36' ) );
	} );

	QUnit.test( 'isAvailable() handles properly supported browsers', function ( assert ) {
		var icon = new DownloadIcon( this.skin, VALID_SUPPORTED_NAMESPACES, windowChrome );
		// Samsung Galaxy S7, Android 6, Chrome 44
		assert.ok( icon.isAvailable( 'Mozilla/5.0 (Linux; Android 6.0.1; SAMSUNG SM-G930F Build/MMB29K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/4.0 Chrome/44.0.2403.133 Mobile Safari/537.36' ) );
		// Samsung Galaxy A5, Android 7, Samsung Browser 5.2
		assert.ok( icon.isAvailable( 'Mozilla/5.0 (Linux; Android 7.0; SAMSUNG SM-A510F Build/NRD90M) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/5.2 Chrome/51.0.2704.106 Mobile Safari/537.36' ) );
		// Galaxy J2, Android 5, Chrome 65
		assert.ok( icon.isAvailable( 'Mozilla/5.0 (Linux; Android 5.1.1; SM-J200G Build/LMY47X) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3320.0 Mobile Safari/537.36' ) );
		// Desktop, Chrome 63
		assert.ok( icon.isAvailable( 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36' ) );
		// Desktop, Ubuntu, Chromium 61
		assert.ok( icon.isAvailable( 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/61.0.3163.100 Chrome/61.0.3163.100 Safari/537.36' ) );
		// Galaxy S8, Android 8, Samsung Browser 6.2
		assert.ok( icon.isAvailable( 'Mozilla/5.0 (Linux; Android 7.0; SAMSUNG SM-G950U1 Build/NRD90M) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/6.2 Chrome/56.0.2924.87 Mobile Safari/537.36' ) );
	} );

}( mw.mobileFrontend ) );
