( function ( M ) {
	var mobile = M.require( 'mobile.startup' ),
		Toggler = mobile.Toggler,
		TableOfContents = mobile.toc.TableOfContents,
		eventBus = mobile.eventBusSingleton;

	/**
	 * Create TableOfContents if the given Page has sections and is not the main page
	 * and wgMFTocEnabled config variable is set to true.
	 * @method
	 * @param {Page} page for which a TOC is generated
	 * @param {jQuery.Object} $toc container to replace
	 * @ignore
	 */
	function init( page, $toc ) {
		var sections = page.getSections(),
			toc = new TableOfContents( {
				sections: sections
			} );

		// eslint-disable-next-line no-new
		new Toggler( {
			$container: toc.$el,
			prefix: 'toc-',
			page: page,
			isClosed: true,
			eventBus: eventBus
		} );
		// if there is a toc already, replace it
		if ( $toc.length > 0 ) {
			// don't show toc at end of page, when no sections there
			$toc.replaceWith( toc.$el );
		} else {
			// otherwise append it to the lead section
			toc.appendTo( page.getLeadSectionElement() );
		}
	}

	module.exports = init;

}( mw.mobileFrontend ) );
