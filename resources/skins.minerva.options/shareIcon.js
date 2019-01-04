( function ( M, msg, config ) {
	var Icon = M.require( 'mobile.startup/Icon' );

	/**
	 * Generate a mouse event that when run
	 * shares the current url with the current page title as the title and text
	 * @param {Navigator} navigator object with method share
	 * @return {Function} mouse event click handler
	 */
	function clickShareHandler( navigator ) {
		return function () {
			navigator.share( {
				title: config.get( 'wgTitle' ),
				text: config.get( 'wgTitle' ),
				url: window.location.href.split( '#' )[ 0 ]
			} );
		};
	}

	/**
	 * Generate a share icon for sharing pages using the navigation share API
	 *
	 * @param {Navigator} navigator object with method share
	 * @return {Icon}
	 */
	function shareIcon( navigator ) {
		return new Icon( {
			tagName: 'li',
			glyphPrefix: 'minerva',
			title: msg( 'skin-minerva-share' ),
			name: 'share',
			events: {
				click: clickShareHandler( navigator )
			}
		} );
	}

	M.define( 'skins.minerva.share/shareIcon', shareIcon );
}( mw.mobileFrontend, mw.msg, mw.config ) );
