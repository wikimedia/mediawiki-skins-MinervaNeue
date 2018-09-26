( function ( M, $ ) {

	var loader = M.require( 'mobile.startup/rlModuleLoader' ),
		features = mw.config.get( 'wgMinervaFeatures', {} ),
		overlayManager = M.require( 'skins.minerva.scripts/overlayManager' ),
		user = M.require( 'mobile.startup/user' );

	// check the categories feature has been turned on
	if ( !features.categories ) {
		return;
	}

	// categories overlay
	overlayManager.add( /^\/categories$/, function () {
		return loader.loadModule( 'mobile.categories.overlays', true ).then( function ( loadingOverlay ) {
			var CategoryOverlay = M.require( 'mobile.categories.overlays/CategoryOverlay' );
			M.on( 'category-added', function () {
				window.location.hash = '#/categories';
			} );

			loadingOverlay.hide();
			return new CategoryOverlay( {
				api: new mw.Api(),
				isAnon: user.isAnon(),
				title: M.getCurrentPage().title
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
				isAnon: user.isAnon(),
				title: M.getCurrentPage().title
			} );
		} );
	} );

	/**
	 * Enable the categories button
	 * @ignore
	 */
	function initButton() {
		$( '.category-button' ).removeClass( 'hidden' );
	}

	$( initButton );

}( mw.mobileFrontend, jQuery ) );
