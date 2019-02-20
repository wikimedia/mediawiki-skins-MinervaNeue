( function ( M ) {
	var drawer,
		skin = M.require( 'skins.minerva.scripts/skin' ),
		page = M.getCurrentPage(),
		router = require( 'mediawiki.router' ),
		mobile = M.require( 'mobile.startup' ),
		ReferencesGateway = mobile.ReferencesGateway,
		ReferencesMobileViewGateway = mobile.ReferencesMobileViewGateway,
		referencesMobileViewGateway = ReferencesMobileViewGateway.getSingleton(),
		ReferencesHtmlScraperGateway = mobile.ReferencesHtmlScraperGateway,
		ReferencesDrawer = mobile.ReferencesDrawer;

	/**
	 * Creates a ReferenceDrawer based on the currently available
	 * ReferenceGateway
	 *
	 * @ignore
	 * @return {ReferencesDrawer}
	 */
	function referenceDrawerFactory() {
		var gateway = null;

		if ( mw.config.get( 'wgMFLazyLoadReferences', false ) ) {
			gateway = referencesMobileViewGateway;
		} else {
			gateway = new ReferencesHtmlScraperGateway( new mw.Api() );
		}

		return new ReferencesDrawer( {
			gateway: gateway
		} );
	}

	/**
	 * Event handler to show reference when a reference link is clicked
	 * @ignore
	 * @param {JQuery.Event} ev Click event of the reference element
	 * @param {ReferencesDrawer} drawer to show the reference in
	 * @param {Page} page
	 */
	function showReference( ev, drawer, page ) {
		var urlComponents, result,
			$dest = $( ev.currentTarget ),
			href = $dest.attr( 'href' );

		ev.preventDefault();

		// If necessary strip the URL portion of the href so we are left with the
		// fragment
		urlComponents = href.split( '#' );
		if ( urlComponents.length > 1 ) {
			href = '#' + urlComponents[ 1 ];
		}
		result = drawer.showReference( href, page, $dest.text() );
		// Previously showReference method returns nothing so we check its truthy
		// Can be removed when I5a7b23f60722eb5017a85c68f38844dd460f8b63 is merged.
		if ( result ) {
			result.then( function () {}, function ( err ) {
				if ( err === ReferencesGateway.ERROR_NOT_EXIST ) {
					router.navigate( href );
				}
			} );
		}

		// don't hide drawer (stop propagation of click) if it is already shown
		// (e.g. click another reference)
		if ( drawer.isVisible() ) {
			ev.stopPropagation();
		} else {
			// flush any existing reference information
			drawer.render( {
				text: undefined
			} );
		}
	}

	/**
	 * Event handler to show reference when a reference link is clicked.
	 * Delegates to `showReference` once the references drawer is ready.
	 *
	 * @ignore
	 * @param {JQuery.Event} ev Click event of the reference element
	 */
	function onClickReference( ev ) {
		if ( !drawer ) {
			drawer = referenceDrawerFactory();
		}
		showReference( ev, drawer, ev.data.page );
	}

	/**
	 * Make references clickable and show a drawer when clicked on.
	 * @ignore
	 * @param {Page} page
	 */
	function setup( page ) {
		var $refs = page.$el.find( 'sup.reference a' );

		if ( $refs.length ) {
			$refs
				.off( 'click.references' )
				.on( 'click.references', {
					page: page
				}, onClickReference );
		}
	}

	setup( page );
	// When references are lazy loaded you'll want to take care of nested references
	skin.on( 'references-loaded', function ( page ) {
		setup( page );
	} );
}( mw.mobileFrontend ) );
