( function ( M ) {
	var
		mobile = M.require( 'mobile.startup' ),
		loader = mobile.rlModuleLoader,
		features = mw.config.get( 'wgMinervaFeatures', {} ),
		overlayManager = M.require( 'skins.minerva.scripts/overlayManager' ),
		eventBus = mobile.eventBusSingleton,
		isAnon = mw.user.isAnon();

	// check the categories feature has been turned on
	if ( !features.categories ) {
		return;
	}

	// categories overlay
	overlayManager.add( /^\/categories$/, function () {
		return loader.loadModule( 'mobile.categories.overlays', true ).then( function ( loadingOverlay ) {
			var categoryOverlay = M.require( 'mobile.categories.overlays/categoryOverlay' );
			eventBus.on( 'category-added', function () {
				window.location.hash = '#/categories';
			} );

			loadingOverlay.hide();
			return categoryOverlay( {
				api: new mw.Api(),
				isAnon: isAnon,
				title: M.getCurrentPage().title,
				eventBus: eventBus
			} );
		} );
	} );

	// add categories overlay
	overlayManager.add( /^\/categories\/add$/, function () {
		return loader.loadModule( 'mobile.categories.overlays' ).then( function ( loadingOverlay ) {
			var CategoryAddOverlay = M.require( 'mobile.categories.overlays/CategoryAddOverlay' );

			loadingOverlay.hide();
			return new CategoryAddOverlay( {
				api: new mw.Api(),
				isAnon: isAnon,
				title: M.getCurrentPage().title,
				eventBus: eventBus
			} );
		} );
	} );

	/**
	 * Enable the categories button
	 * @ignore
	 */
	function initButton() {
		// eslint-disable-next-line no-jquery/no-global-selector
		$( '.category-button' ).removeClass( 'hidden' );
	}

	$( initButton );

}( mw.mobileFrontend ) );
