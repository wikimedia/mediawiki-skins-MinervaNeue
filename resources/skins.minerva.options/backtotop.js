module.exports = function () {
	var BackToTopOverlay = require( './BackToTopOverlay.js' ),
		backtotop = new BackToTopOverlay(),
		features = mw.config.get( 'wgMinervaFeatures', {} ),
		M = mw.mobileFrontend,
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
};
