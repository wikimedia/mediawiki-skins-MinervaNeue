module.exports = function () {
	var M = mw.mobileFrontend,
		mobile = M.require( 'mobile.startup' ),
		references = mobile.references,
		currentPage = mobile.currentPage(),
		currentPageHTMLParser = mobile.currentPageHTMLParser(),
		ReferencesHtmlScraperGateway = mobile.ReferencesHtmlScraperGateway,
		gateway = new ReferencesHtmlScraperGateway( new mw.Api() );

	/**
	 * Event handler to show reference when a reference link is clicked
	 * @ignore
	 * @param {JQuery.Event} ev Click event of the reference element
	 */
	function showReference( ev ) {
		var urlComponents,
			$dest = $( ev.currentTarget ),
			href = $dest.attr( 'href' );

		ev.preventDefault();

		// If necessary strip the URL portion of the href so we are left with the
		// fragment
		urlComponents = href.split( '#' );
		if ( urlComponents.length > 1 ) {
			href = '#' + urlComponents[ 1 ];
		}
		references.showReference( href, currentPage, $dest.text(),
			currentPageHTMLParser, gateway );
	}

	/**
	 * Event handler to show reference when a reference link is clicked.
	 * Delegates to `showReference` once the references drawer is ready.
	 *
	 * @ignore
	 * @param {JQuery.Event} ev Click event of the reference element
	 */
	function onClickReference( ev ) {
		showReference( ev );
	}

	function init() {
		// Make references clickable and show a drawer when clicked on.
		mobile.util.getDocument().on( 'click', 'sup.reference a', onClickReference );
	}

	init();
};
