( function ( M, msg, config ) {
	var GLYPH = 'share',
		Icon = M.require( 'mobile.startup/Icon' );

	/**
	 * A download icon for triggering print functionality
	 * @class ShareIcon
	 * @extends Icon

	 * @constructor
	 */
	function ShareIcon() {
		var options = {};
		options.tagName = 'li';
		options.glyphPrefix = 'minerva';
		options.title = msg( 'skin-minerva-share' );
		options.name = GLYPH;
		Icon.call( this, options );
	}

	OO.mfExtend( ShareIcon, Icon, {
		/**
		 * onClick handler for button that invokes print function
		 *
		 * @memberof ShareIcon
		 * @instance
		 */
		onClick: function () {
			navigator.share( {
				title: config.get( 'wgTitle' ),
				text: config.get( 'wgTitle' ),
				url: window.location.href.split( '#' )[ 0 ]
			} );
		},
		events: {
			click: 'onClick'
		}
	} );

	M.define( 'skins.minerva.share/ShareIcon', ShareIcon );
}( mw.mobileFrontend, mw.msg, mw.config ) );
