( function ( M ) {

	var msg = mw.msg,
		MAX_PRINT_TIMEOUT = 3000,
		GLYPH = 'mf-download',
		Icon = M.require( 'mobile.startup/Icon' );

	/**
	 * A download icon for triggering print functionality
	 * @class DownloadIcon
	 * @extends Icon
	 *
	 * @param {Skin} skin
	 * @constructor
	 */
	function DownloadIcon( skin ) {
		var options = {};
		this.skin = skin;
		options.tagName = 'li';
		options.title = msg( 'minerva-download' );
		options.name = GLYPH;
		Icon.call( this, options );
	}

	OO.mfExtend( DownloadIcon, Icon, {
		/**
		 * Replace download icon with a spinner
		 */
		showSpinner: function () {
			this.options.name = 'spinner';
			this.render();
		},
		/**
		 * Restore download icon from spinner state
		 */
		hideSpinner: function () {
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
				window.print();
				hideSpinner();
			}

			// The click handler may be invoked multiple times so if a pending print is occurring
			// do nothing.
			if ( !this.timeout ) {
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
}( mw.mobileFrontend ) );
