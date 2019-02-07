( function ( M ) {
	var BackToTopOverlay = M.require( 'skins.minerva.options/BackToTopOverlay' ),
		backtotop = new BackToTopOverlay(),
		features = mw.config.get( 'wgMinervaFeatures', {} ),
		mobile = M.require( 'mobile.startup' ),
		browser = mobile.Browser.getSingleton(),
		eventBus = mobile.eventBusSingleton;

	// check if browser user agent is iOS (T141598)
	if ( browser.isIos() || !features.backToTop ) {
		return;
	}

	// initialize the back to top element
	backtotop.appendTo( 'body' );

	eventBus.on( 'scroll', function () {
		if ( $( window ).height() - $( window ).scrollTop() <= 0 ) {
			backtotop.show();
		} else {
			backtotop.hide();
		}
	} );
}( mw.mobileFrontend ) );
