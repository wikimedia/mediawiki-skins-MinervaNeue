( function ( M, track ) {
	var msg = mw.msg,
		MAX_PRINT_TIMEOUT = 3000,
		GLYPH = 'download',
		Icon = M.require( 'mobile.startup/Icon' ),
		browser = M.require( 'mobile.startup/Browser' ).getSingleton();

	/**
	 * Helper function to retreive the Android version
	 * @ignore
	 * @param {String} userAgent User Agent
	 * @return {Integer}
	 */
	function getAndroidVersion( userAgent ) {
		var match = userAgent.toLowerCase().match( /android\s(\d\.]*)/ );
		return match ? parseInt( match[1] ) : false;
	}

	/**
	 * Helper function to retrieve the Chrome/Chromium version
	 * @ignore
	 * @param {String} userAgent User Agent
	 * @return {Integer}
	 */
	function getChromeVersion( userAgent ) {
		var match = userAgent.toLowerCase().match( /chrom(e|ium)\/(\d+)\./ );
		return match ? parseInt( match[2] ) : false;
	}

	/**
	 * A download icon for triggering print functionality
	 * @class DownloadIcon
	 * @extends Icon
	 *
	 * @param {Skin} skin
	 * @param {Number[]} [supportedNamespaces]
	 * @param {Window} [windowObj] window object
	 * @constructor
	 * @module skins.minerva.scripts/DownloadIcon
	 */
	function DownloadIcon( skin, supportedNamespaces, windowObj ) {
		var options = {};
		this.skin = skin;
		this.window = windowObj || {};
		this.supportedNamespaces = supportedNamespaces || [ 0 ];
		options.tagName = 'li';
		options.glyphPrefix = 'minerva';
		options.title = msg( 'minerva-download' );
		options.name = GLYPH;
		Icon.call( this, options );
	}

	OO.mfExtend( DownloadIcon, Icon, {
		/**
		 * Checks whether DownloadIcon is available for given user agent
		 * @param {string} userAgent User agent
		 * @return {Boolean}
		*/
		isAvailable: function ( userAgent ) {
			var androidVersion = getAndroidVersion( userAgent ),
				chromeVersion = getChromeVersion( userAgent ),
				page = this.skin.page;

			// Download button is restricted to certain namespaces T181152.
			// Defaults to 0, in case cached JS has been served.
			if ( this.supportedNamespaces.indexOf( page.getNamespaceId() ) === -1 ||
				page.isMainPage() ) {
				// namespace is not supported or it's a main page
				return false;
			}

			if ( browser.isIos() || chromeVersion === false ||
				this.window.chrome === undefined
			) {
				// we support only chrome/chromium on desktop/android
				return false;
			}
			if ( ( androidVersion && androidVersion < 5 ) || chromeVersion < 41 ) {
				return false;
			}
			return true;
		},

		/**
		 * Replace download icon with a spinner
		 */
		showSpinner: function () {
			// FIXME: There is no spinner icon in Minerva, only in MobileFrontend
			// Hopefully when T177432 is resolved this and corresponding change in hideSpinner
			// should be unnecessary.
			this.options.glyphPrefix = 'mf';
			this.options.name = 'spinner';
			this.render();
		},
		/**
		 * Restore download icon from spinner state
		 */
		hideSpinner: function () {
			this.options.glyphPrefix = 'minerva';
			this.options.name = GLYPH;
			this.render();
		},
		isTemplateMode: false,
		/**
		 * onClick handler for button that invokes print function
		 */
		onClick: function () {
			var self = this,
				hideSpinner = this.hideSpinner.bind( this );

			function doPrint() {
				self.timeout = clearTimeout( self.timeout );
				track( 'minerva.downloadAsPDF', {
					action: 'callPrint'
				} );
				window.print();
				hideSpinner();
			}
			// The click handler may be invoked multiple times so if a pending print is occurring
			// do nothing.
			if ( !this.timeout ) {
				track( 'minerva.downloadAsPDF', {
					action: 'fetchImages'
				} );
				this.showSpinner();
				// If all image downloads are taking longer to load then the MAX_PRINT_TIMEOUT
				// abort the spinner and print regardless.
				this.timeout = setTimeout( doPrint, MAX_PRINT_TIMEOUT );
				this.skin.loadImagesList().always( function () {
					if ( self.timeout ) {
						doPrint();
					}
				} );
			}
		},
		events: {
			click: 'onClick'
		}
	} );

	M.define( 'skins.minerva.scripts/DownloadIcon', DownloadIcon );
}( mw.mobileFrontend, mw.track ) );
