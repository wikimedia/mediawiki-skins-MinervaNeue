( function ( M ) {

	var msg = mw.msg,
		Icon = M.require( 'mobile.startup/Icon' );

	/**
	 * A download icon for triggering print functionality
	 * @class DownloadIcon
	 * @extends Icon
	 *
	 * @constructor
	 */
	function DownloadIcon() {
		var options = {};
		options.tagName = 'li';
		options.title = msg( 'minerva-download' );
		options.name = 'download';
		Icon.call( this, options );
	}

	OO.mfExtend( DownloadIcon, Icon, {
		onClick: function () {
			window.print();
		},
		events: {
			click: 'onClick'
		}
	} );

	M.define( 'skins.minerva.scripts/DownloadIcon', DownloadIcon );
}( mw.mobileFrontend ) );
