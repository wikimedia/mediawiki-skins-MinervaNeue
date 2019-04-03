( function ( M ) {

	var Watchstar = M.require( 'mobile.startup' ).Watchstar,
		user = mw.user;

	/**
	 * Toggle the watch status of a known page
	 * @method
	 * @param {Page} page
	 * @ignore
	 */
	function init( page ) {
		// eslint-disable-next-line no-jquery/no-global-selector
		var $container = $( '#ca-watch' );
		if ( !page.inNamespace( 'special' ) ) {
			// eslint-disable-next-line no-new
			new Watchstar( {
				api: new mw.Api(),
				el: $container,
				isWatched: page.isWatched(),
				page: page,
				funnel: 'page',
				isAnon: user.isAnon()
			} );
		}
	}
	init( M.getCurrentPage() );

}( mw.mobileFrontend ) );
