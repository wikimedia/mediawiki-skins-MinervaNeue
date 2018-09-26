( function ( M, $ ) {
	var BackToTopOverlay = M.require( 'skins.minerva.options/BackToTopOverlay' ),
		backtotop = new BackToTopOverlay(),
		features = mw.config.get( 'wgMinervaFeatures', {} ),
		browser = M.require( 'mobile.startup/Browser' ).getSingleton();

	// check if browser user agent is iOS (T141598)
	if ( browser.isIos() || !features.backToTop ) {
		return;
	}

	// initialize the back to top element
	backtotop.appendTo( 'body' );

	M.on( 'scroll', function () {
		if ( $( window ).height() - $( window ).scrollTop() <= 0 ) {
			backtotop.show();
		} else {
			backtotop.hide();
		}
	} );
}( mw.mobileFrontend, jQuery ) );
